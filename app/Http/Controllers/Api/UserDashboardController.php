<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductReturn;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserDashboardController extends Controller
{
    /**
     * Get user dashboard statistics.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Order statistics
        $orderStats = Order::where('user_id', $user->id)
            ->select([
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_orders'),
                DB::raw('SUM(CASE WHEN status = "processing" THEN 1 ELSE 0 END) as processing_orders'),
                DB::raw('SUM(CASE WHEN status = "delivered" THEN 1 ELSE 0 END) as delivered_orders'),
                DB::raw('SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled_orders'),
                DB::raw('SUM(total_amount) as total_spent'),
            ])
            ->first();

        // Recent orders
        $recentOrders = Order::where('user_id', $user->id)
            ->with(['items.product'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Pending reviews (delivered orders without reviews)
        $pendingReviews = Order::where('user_id', $user->id)
            ->where('status', 'delivered')
            ->whereDoesntHave('items.reviews', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->count();

        // Active returns
        $activeReturns = ProductReturn::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved', 'processing'])
            ->count();

        // Wishlist count
        $wishlistCount = $user->wishlists()->count();

        // Preorder balance
        $preorderBalance = Order::where('user_id', $user->id)
            ->where('order_type', 'preorder')
            ->where('payment_status', 'partial')
            ->sum('remaining_amount');

        return response()->json([
            'success' => true,
            'data' => [
                'statistics' => [
                    'total_orders' => $orderStats->total_orders ?? 0,
                    'pending_orders' => $orderStats->pending_orders ?? 0,
                    'processing_orders' => $orderStats->processing_orders ?? 0,
                    'delivered_orders' => $orderStats->delivered_orders ?? 0,
                    'cancelled_orders' => $orderStats->cancelled_orders ?? 0,
                    'total_spent' => $orderStats->total_spent ?? 0,
                    'pending_reviews' => $pendingReviews,
                    'active_returns' => $activeReturns,
                    'wishlist_count' => $wishlistCount,
                    'preorder_balance' => $preorderBalance,
                ],
                'recent_orders' => $recentOrders,
            ],
        ]);
    }

    /**
     * Get user profile with addresses.
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user()->load(['addresses']);

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }
}
