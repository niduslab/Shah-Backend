<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Get sales report.
     */
    public function sales(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'group_by' => 'nullable|in:day,week,month,year',
        ]);

        $dateFrom = $validated['date_from'] ?? now()->subMonth();
        $dateTo = $validated['date_to'] ?? now();
        $groupBy = $validated['group_by'] ?? 'day';

        $dateFormat = match ($groupBy) {
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            'year' => '%Y',
        };

        $sales = Order::where('status', '!=', 'cancelled')
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->select([
                DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as period"),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total_amount) as total_sales'),
                DB::raw('SUM(discount_amount) as total_discounts'),
                DB::raw('AVG(total_amount) as average_order_value'),
            ])
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        // Summary stats
        $summary = Order::where('status', '!=', 'cancelled')
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->select([
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total_amount) as total_revenue'),
                DB::raw('SUM(discount_amount) as total_discounts'),
                DB::raw('AVG(total_amount) as average_order_value'),
            ])
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => $summary,
                'chart_data' => $sales,
                'date_range' => [
                    'from' => $dateFrom,
                    'to' => $dateTo,
                ],
            ],
        ]);
    }

    /**
     * Get product performance report.
     */
    public function products(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $dateFrom = $validated['date_from'] ?? now()->subMonth();
        $dateTo = $validated['date_to'] ?? now();
        $limit = $validated['limit'] ?? 20;

        $topProducts = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', '!=', 'cancelled')
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.created_at', [$dateFrom, $dateTo])
            ->select([
                'order_items.product_id',
                'order_items.product_name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.total_price) as total_revenue'),
                DB::raw('COUNT(DISTINCT orders.id) as order_count'),
            ])
            ->groupBy('order_items.product_id', 'order_items.product_name')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get();

        // Low stock products
        $lowStock = Product::whereRaw('quantity <= low_stock_threshold')
            ->where('status', 'active')
            ->select(['id', 'name', 'sku', 'quantity', 'low_stock_threshold'])
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'top_products' => $topProducts,
                'low_stock' => $lowStock,
            ],
        ]);
    }

    /**
     * Get customer report.
     */
    public function customers(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $dateFrom = $validated['date_from'] ?? now()->subMonth();
        $dateTo = $validated['date_to'] ?? now();
        $limit = $validated['limit'] ?? 20;

        // Top customers
        $topCustomers = Order::where('status', '!=', 'cancelled')
            ->where('payment_status', 'paid')
            ->whereNotNull('user_id')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->select([
                'user_id',
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total_amount) as total_spent'),
                DB::raw('AVG(total_amount) as average_order'),
            ])
            ->groupBy('user_id')
            ->orderByDesc('total_spent')
            ->limit($limit)
            ->with('user:id,name,email')
            ->get();

        // New customers
        $newCustomers = User::where('user_type', 'customer')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->count();

        // Customer summary
        $totalCustomers = User::where('user_type', 'customer')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'top_customers' => $topCustomers,
                'new_customers' => $newCustomers,
                'total_customers' => $totalCustomers,
            ],
        ]);
    }

    /**
     * Get inventory report.
     */
    public function inventory(): JsonResponse
    {
        $summary = [
            'total_products' => Product::count(),
            'active_products' => Product::where('status', 'active')->count(),
            'out_of_stock' => Product::where('quantity', 0)->count(),
            'low_stock' => Product::whereRaw('quantity <= low_stock_threshold')
                ->where('quantity', '>', 0)
                ->count(),
            'total_stock_value' => Product::selectRaw('SUM(quantity * COALESCE(cost_price, price)) as value')
                ->value('value') ?? 0,
        ];

        // Stock by category
        $byCategory = Product::join('categories', 'products.category_id', '=', 'categories.id')
            ->select([
                'categories.name as category',
                DB::raw('COUNT(products.id) as product_count'),
                DB::raw('SUM(products.quantity) as total_stock'),
            ])
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_stock')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => $summary,
                'by_category' => $byCategory,
            ],
        ]);
    }

    /**
     * Get order status report.
     */
    public function orderStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ]);

        $dateFrom = $validated['date_from'] ?? now()->subMonth();
        $dateTo = $validated['date_to'] ?? now();

        $statusCounts = Order::whereBetween('created_at', [$dateFrom, $dateTo])
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $paymentStatusCounts = Order::whereBetween('created_at', [$dateFrom, $dateTo])
            ->select('payment_status', DB::raw('COUNT(*) as count'))
            ->groupBy('payment_status')
            ->pluck('count', 'payment_status');

        $orderTypeCounts = Order::whereBetween('created_at', [$dateFrom, $dateTo])
            ->select('order_type', DB::raw('COUNT(*) as count'))
            ->groupBy('order_type')
            ->pluck('count', 'order_type');

        return response()->json([
            'success' => true,
            'data' => [
                'by_status' => $statusCounts,
                'by_payment_status' => $paymentStatusCounts,
                'by_order_type' => $orderTypeCounts,
            ],
        ]);
    }

    /**
     * Get dashboard summary.
     */
    public function dashboard(): JsonResponse
    {
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();

        // Today's stats
        $todayStats = Order::whereDate('created_at', $today)
            ->where('status', '!=', 'cancelled')
            ->select([
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(total_amount) as revenue'),
            ])
            ->first();

        // This month's stats
        $monthStats = Order::where('created_at', '>=', $thisMonth)
            ->where('status', '!=', 'cancelled')
            ->where('payment_status', 'paid')
            ->select([
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(total_amount) as revenue'),
            ])
            ->first();

        // Pending items
        $pending = [
            'orders' => Order::where('status', 'pending')->count(),
            'reviews' => \App\Models\Review::where('status', 'pending')->count(),
            'returns' => \App\Models\ProductReturn::where('status', 'pending')->count(),
        ];

        // Low stock alert
        $lowStockCount = Product::whereRaw('quantity <= low_stock_threshold')
            ->where('status', 'active')
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'today' => $todayStats,
                'this_month' => $monthStats,
                'pending' => $pending,
                'low_stock_count' => $lowStockCount,
            ],
        ]);
    }
}
