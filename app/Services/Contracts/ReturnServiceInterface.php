<?php

namespace App\Services\Contracts;

use App\Models\OrderItem;
use App\Models\ProductReturn;
use App\Models\User;

interface ReturnServiceInterface
{
    /**
     * Create a return request.
     */
    public function createReturnRequest(OrderItem $orderItem, User $user, int $quantity, string $reason, ?string $details = null): ProductReturn;

    /**
     * Approve a return request.
     */
    public function approveReturn(ProductReturn $return, ?string $adminNotes = null): ProductReturn;

    /**
     * Reject a return request.
     */
    public function rejectReturn(ProductReturn $return, string $reason): ProductReturn;

    /**
     * Process completed return (restore inventory).
     */
    public function processReturn(ProductReturn $return): ProductReturn;

    /**
     * Get return requests for a user.
     */
    public function getUserReturns(User $user): \Illuminate\Support\Collection;
}
