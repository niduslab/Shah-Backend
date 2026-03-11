<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use App\Services\Contracts\ReviewServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(
        protected ReviewServiceInterface $reviewService
    ) {}

    /**
     * Get reviews for a product.
     */
    public function productReviews(int $productId): JsonResponse
    {
        $product = Product::find($productId);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
            ], 404);
        }

        $reviews = Review::where('product_id', $productId)
            ->approved()
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $stats = [
            'average_rating' => $this->reviewService->calculateAverageRating($productId),
            'total_reviews' => Review::where('product_id', $productId)->approved()->count(),
            'rating_distribution' => Review::where('product_id', $productId)
                ->approved()
                ->selectRaw('rating, COUNT(*) as count')
                ->groupBy('rating')
                ->pluck('count', 'rating'),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'reviews' => $reviews,
                'stats' => $stats,
            ],
        ]);
    }

    /**
     * Submit a review.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'comment' => 'required|string|max:2000',
        ]);

        $user = $request->user();

        // Check if user has purchased the product
        $hasPurchased = $this->reviewService->hasPurchasedProduct($user, $validated['product_id']);

        if (!$hasPurchased) {
            return response()->json([
                'success' => false,
                'message' => 'You can only review products you have purchased.',
            ], 403);
        }

        // Check if user already reviewed this product
        $existingReview = Review::where('user_id', $user->id)
            ->where('product_id', $validated['product_id'])
            ->first();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reviewed this product.',
            ], 400);
        }

        $review = $this->reviewService->createReview($user, $validated);

        // Notify admins about new review
        app(\App\Services\NotificationService::class)->notifyNewReview($review);

        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully. It will be visible after approval.',
            'data' => $review,
        ], 201);
    }

    /**
     * Mark a review as helpful.
     */
    public function markHelpful(Request $request, int $reviewId): JsonResponse
    {
        $review = Review::approved()->find($reviewId);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found.',
            ], 404);
        }

        $user = $request->user();

        $result = $this->reviewService->markHelpful($review, $user);

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => [
                'helpful_count' => $review->fresh()->helpful_count,
            ],
        ]);
    }

    /**
     * Get user's reviews.
     */
    public function myReviews(Request $request): JsonResponse
    {
        $reviews = Review::where('user_id', $request->user()->id)
            ->with('product:id,name,slug')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $reviews,
        ]);
    }
}
