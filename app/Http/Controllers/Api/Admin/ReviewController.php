<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Services\Contracts\ReviewServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function __construct(
        protected ReviewServiceInterface $reviewService
    ) {}

    /**
     * List all reviews.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Review::with(['user', 'product']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('comment', 'like', "%{$search}%");
            });
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 15);
        $reviews = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $reviews,
        ]);
    }

    /**
     * Get a specific review.
     */
    public function show(int $id): JsonResponse
    {
        $review = Review::with(['user', 'product'])->find($id);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $review,
        ]);
    }

    /**
     * Approve a review.
     */
    public function approve(int $id): JsonResponse
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found.',
            ], 404);
        }

        $review = $this->reviewService->approveReview($review);

        return response()->json([
            'success' => true,
            'message' => 'Review approved.',
            'data' => $review,
        ]);
    }

    /**
     * Reject a review.
     */
    public function reject(int $id): JsonResponse
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found.',
            ], 404);
        }

        $review = $this->reviewService->rejectReview($review);

        return response()->json([
            'success' => true,
            'message' => 'Review rejected.',
            'data' => $review,
        ]);
    }

    /**
     * Add admin response to a review.
     */
    public function respond(Request $request, int $id): JsonResponse
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found.',
            ], 404);
        }

        $validated = $request->validate([
            'admin_response' => 'required|string|max:1000',
        ]);

        $review->update(['admin_response' => $validated['admin_response']]);

        // Notify customer about admin response
        app(\App\Services\NotificationService::class)->notifyReviewResponse($review);

        return response()->json([
            'success' => true,
            'message' => 'Response added to review.',
            'data' => $review,
        ]);
    }

    /**
     * Delete a review.
     */
    public function destroy(int $id): JsonResponse
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found.',
            ], 404);
        }

        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Review deleted.',
        ]);
    }

    /**
     * Get review statistics.
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total' => Review::count(),
            'pending' => Review::where('status', 'pending')->count(),
            'approved' => Review::where('status', 'approved')->count(),
            'rejected' => Review::where('status', 'rejected')->count(),
            'average_rating' => Review::where('status', 'approved')->avg('rating'),
            'rating_distribution' => Review::where('status', 'approved')
                ->select('rating', DB::raw('COUNT(*) as count'))
                ->groupBy('rating')
                ->pluck('count', 'rating'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
