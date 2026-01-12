<?php

namespace App\Services\Contracts;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;

interface ReviewServiceInterface
{
    /**
     * Create a review with purchase verification.
     */
    public function createReview(User $user, Product $product, int $rating, ?string $title = null, ?string $comment = null): Review;

    /**
     * Approve a review.
     */
    public function approveReview(Review $review): Review;

    /**
     * Reject a review.
     */
    public function rejectReview(Review $review): Review;

    /**
     * Mark review as helpful.
     */
    public function markHelpful(Review $review, User $user): bool;

    /**
     * Calculate average rating for a product.
     */
    public function calculateAverageRating(Product $product): float;

    /**
     * Add admin response to review.
     */
    public function addAdminResponse(Review $review, string $response): Review;
}
