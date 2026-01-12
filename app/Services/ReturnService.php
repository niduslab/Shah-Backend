<?php

namespace App\Services;

use App\Models\OrderItem;
use App\Models\ProductReturn;
use App\Models\User;
use App\Services\Contracts\InventoryServiceInterface;
use App\Services\Contracts\ReturnServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReturnService implements ReturnServiceInterface
{
    public function __construct(
        protected InventoryServiceInterface $inventoryService
    ) {}

    /**
     * Create a return request.
     * 
     * @param OrderItem $orderItem
     * @param User $user
     * @param int $quantity
     * @param string $reason
     * @param string|null $details
     * @return ProductReturn
     */
    public function createReturnRequest(
        OrderItem $orderItem,
        User $user,
        int $quantity,
        string $reason,
        ?string $details = null
    ): ProductReturn {
        // Validate quantity
        if ($quantity > $orderItem->quantity) {
            throw new \InvalidArgumentException('Return quantity cannot exceed ordered quantity.');
        }

        // Check if return already exists for this item
        $existingReturn = ProductReturn::where('order_item_id', $orderItem->id)
            ->whereNotIn('status', ['rejected', 'processed'])
            ->first();

        if ($existingReturn) {
            throw new \InvalidArgumentException('A return request already exists for this item.');
        }

        return ProductReturn::create([
            'order_id' => $orderItem->order_id,
            'order_item_id' => $orderItem->id,
            'user_id' => $user->id,
            'quantity' => $quantity,
            'reason' => $reason,
            'reason_details' => $details,
            'status' => 'requested',
        ]);
    }

    /**
     * Approve a return request.
     * 
     * @param ProductReturn $return
     * @param string|null $adminNotes
     * @return ProductReturn
     */
    public function approveReturn(ProductReturn $return, ?string $adminNotes = null): ProductReturn
    {
        if ($return->status !== 'requested') {
            throw new \InvalidArgumentException('Only requested returns can be approved.');
        }

        $return->update([
            'status' => 'approved',
            'admin_notes' => $adminNotes,
        ]);

        // Update order status
        $return->order->update(['status' => 'return_approved']);

        return $return->fresh();
    }

    /**
     * Reject a return request.
     * 
     * @param ProductReturn $return
     * @param string $reason
     * @return ProductReturn
     */
    public function rejectReturn(ProductReturn $return, string $reason): ProductReturn
    {
        if ($return->status !== 'requested') {
            throw new \InvalidArgumentException('Only requested returns can be rejected.');
        }

        $return->update([
            'status' => 'rejected',
            'admin_notes' => $reason,
        ]);

        return $return->fresh();
    }

    /**
     * Mark return as received.
     * 
     * @param ProductReturn $return
     * @return ProductReturn
     */
    public function markAsReceived(ProductReturn $return): ProductReturn
    {
        if ($return->status !== 'approved') {
            throw new \InvalidArgumentException('Only approved returns can be marked as received.');
        }

        $return->update(['status' => 'received']);

        return $return->fresh();
    }

    /**
     * Process completed return (restore inventory).
     * 
     * @param ProductReturn $return
     * @return ProductReturn
     */
    public function processReturn(ProductReturn $return): ProductReturn
    {
        if (!in_array($return->status, ['approved', 'received'])) {
            throw new \InvalidArgumentException('Only approved or received returns can be processed.');
        }

        return DB::transaction(function () use ($return) {
            $orderItem = $return->orderItem;

            // Restore inventory
            $this->inventoryService->adjustStock(
                $orderItem->product,
                $return->quantity,
                'return',
                $orderItem->productVariation,
                "Return #{$return->id} processed"
            );

            $return->update(['status' => 'processed']);

            return $return->fresh();
        });
    }

    /**
     * Get return requests for a user.
     * 
     * @param User $user
     * @return Collection
     */
    public function getUserReturns(User $user): Collection
    {
        return ProductReturn::where('user_id', $user->id)
            ->with(['order', 'orderItem.product'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get all pending return requests.
     * 
     * @return Collection
     */
    public function getPendingReturns(): Collection
    {
        return ProductReturn::where('status', 'requested')
            ->with(['order', 'orderItem.product', 'user'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Get return by ID.
     * 
     * @param int $id
     * @return ProductReturn|null
     */
    public function getReturnById(int $id): ?ProductReturn
    {
        return ProductReturn::with(['order', 'orderItem.product', 'user'])
            ->find($id);
    }
}
