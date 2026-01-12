<?php

namespace App\Services;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Services\Contracts\ReviewServiceInterface;
use Illuminate\Support\Facades\DB;

class ReviewService implements ReviewServiceInterface
{
    /**
     * Create a review with purchase verification.
     * 
     * @param User $user
     * @param Product $product
     * @param int $rating
     * @param string|null $title
     * @param string|null $comment
     * @return Review
     */
    public function createReview(
        User $user,
        Product $product,
        int $rating,
        ?string $title = null,
        ?string $comment = null
    ): Review {
        // Validate rating range
        if ($rating < 1 || $rating > 5) {
            throw new \InvalidArgumentException('Rating must be between 1 and 5.');
        }

        // Verify purchase
        $orderItem = $this->verifyPurchase($user, $product);
        if (!$orderItem) {
            throw new \InvalidArgumentException('You can only review products you have purchased.');
        }

        // Check if user already reviewed this product
        $existingReview = Review::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existingReview) {
            throw new \InvalidArgumentException('You have already reviewed this product.');
        }

        return Review::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $orderItem->order_id,
            'rating' => $rating,
            'title' => $title,
            'comment' => $comment,
            'status' => 'pending',
        ]);
    }

    /**
     * Approve a review.
     * 
     * @param Review $review
     * @return Review
     */
    public function approveReview(Review $review): Review
    {
        $review->update(['status' => 'approved']);
        return $review->fresh();
    }

    /**
     * Reject a review.
     * 
     * @param Review $review
     * @return Review
     */
    public function rejectReview(Review $review): Review
    {
        $review->update(['status' => 'rejected']);
        return $review->fresh();
    }

    /**
     * Mark review as helpful.
     * 
     * @param Review $review
     * @param User $user
     * @return bool
     */
    public function markHelpful(Review $review, User $user): bool
    {
        // Check if user already marked this review as helpful
        $exists = DB::table('review_helpful')
            ->where('review_id', $review->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($exists) {
            // Remove helpful mark
            DB::table('review_helpful')
                ->where('review_id', $review->id)
                ->where('user_id', $user->id)
                ->delete();

            $review->decrement('helpful_count');
            return false;
        }

        // Add helpful mark
        DB::table('review_helpful')->insert([
            'review_id' => $review->id,
            'user_id' => $user->id,
        ]);

        $review->increment('helpful_count');
        return true;
    }

    /**
     * Calculate average rating for a product.
     * 
     * @param Product $product
     * @return float
     */
    public function calculateAverageRating(Product $product): float
    {
        $average = Review::where('product_id', $product->id)
            ->where('status', 'approved')
            ->avg('rating');

        return round($average ?? 0, 1);
    }

    /**
     * Add admin response to review.
     * 
     * @param Review $review
     * @param string $response
     * @return Review
     */
    public function addAdminResponse(Review $review, string $response): Review
    {
        $review->update(['admin_response' => $response]);
        return $review->fresh();
    }

    /**
     * Verify user has purchased the product.
     * 
     * @param User $user
     * @param Product $product
     * @return OrderItem|null
     */
    protected function verifyPurchase(User $user, Product $product): ?OrderItem
    {
        return OrderItem::whereHas('order', function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->where('status', 'delivered');
        })
            ->where('product_id', $product->id)
            ->first();
    }

    /**
     * Get reviews for a product.
     * 
     * @param Product $product
     * @param bool $approvedOnly
     * @return \Illuminate\Support\Collection
     */
    public function getProductReviews(Product $product, bool $approvedOnly = true)
    {
        $query = Review::where('product_id', $product->id)
            ->with('user')
            ->orderBy('created_at', 'desc');

        if ($approvedOnly) {
            $query->where('status', 'approved');
        }

        return $query->get();
    }

    /**
     * Get pending reviews.
     * 
     * @return \Illuminate\Support\Collection
     */
    public function getPendingReviews()
    {
        return Review::where('status', 'pending')
            ->with(['user', 'product'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Get review statistics for a product.
     * 
     * @param Product $product
     * @return array
     */
    public function getProductReviewStats(Product $product): array
    {
        $reviews = Review::where('product_id', $product->id)
            ->where('status', 'approved')
            ->get();

        $total = $reviews->count();
        $average = $total > 0 ? round($reviews->avg('rating'), 1) : 0;

        $distribution = [];
        for ($i = 5; $i >= 1; $i--) {
            $count = $reviews->where('rating', $i)->count();
            $distribution[$i] = [
                'count' => $count,
                'percentage' => $total > 0 ? round(($count / $total) * 100) : 0,
            ];
        }

        return [
            'total' => $total,
            'average' => $average,
            'distribution' => $distribution,
        ];
    }
}
