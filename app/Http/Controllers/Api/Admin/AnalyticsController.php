<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\CartEvent;
use App\Models\CheckoutFunnel;
use App\Models\PageView;
use App\Models\ProductView;
use App\Models\SearchQuery;
use App\Models\VisitorSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Get analytics dashboard overview.
     */
    public function dashboard(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->subDays(30)->startOfDay());
        $dateTo = $request->input('date_to', now()->endOfDay());

        $data = [
            'visitors' => $this->getVisitorStats($dateFrom, $dateTo),
            'page_views' => $this->getPageViewStats($dateFrom, $dateTo),
            'products' => $this->getProductStats($dateFrom, $dateTo),
            'checkout_funnel' => $this->getCheckoutFunnelStats($dateFrom, $dateTo),
            'cart_events' => $this->getCartEventStats($dateFrom, $dateTo),
            'search' => $this->getSearchStats($dateFrom, $dateTo),
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get visitor statistics.
     */
    public function visitors(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $deviceType = $request->input('device_type');
        $authenticated = $request->input('authenticated');

        $query = VisitorSession::with('user')
            ->orderBy('last_activity_at', 'desc');

        if ($dateFrom && $dateTo) {
            $query->dateRange($dateFrom, $dateTo);
        }

        if ($deviceType) {
            $query->where('device_type', $deviceType);
        }

        if ($authenticated === 'true' || $authenticated === '1') {
            $query->authenticated();
        } elseif ($authenticated === 'false' || $authenticated === '0') {
            $query->guest();
        }

        $visitors = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $visitors,
        ]);
    }

    /**
     * Get visitor session details.
     */
    public function visitorDetails($id)
    {
        $session = VisitorSession::with([
            'user',
            'pageViews.product',
            'pageViews.category',
            'productViews.product',
            'cartEvents.product',
            'checkoutFunnel',
            'searchQueries',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $session,
        ]);
    }

    /**
     * Get product view analytics.
     */
    public function productViews(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $sortBy = $request->input('sort_by', 'views'); // views, conversions, purchases

        $query = ProductView::select(
            'product_id',
            DB::raw('COUNT(*) as total_sessions'),
            DB::raw('SUM(view_count) as total_views'),
            DB::raw('SUM(time_spent_seconds) as total_time_spent'),
            DB::raw('AVG(time_spent_seconds) as avg_time_spent'),
            DB::raw('SUM(CASE WHEN added_to_cart = 1 THEN 1 ELSE 0 END) as cart_additions'),
            DB::raw('SUM(CASE WHEN purchased = 1 THEN 1 ELSE 0 END) as purchases'),
            DB::raw('(SUM(CASE WHEN added_to_cart = 1 THEN 1 ELSE 0 END) / COUNT(*) * 100) as cart_conversion_rate'),
            DB::raw('(SUM(CASE WHEN purchased = 1 THEN 1 ELSE 0 END) / COUNT(*) * 100) as purchase_conversion_rate')
        )
            ->with('product:id,name,slug,sku,price')
            ->groupBy('product_id');

        if ($dateFrom && $dateTo) {
            $query->dateRange($dateFrom, $dateTo);
        }

        switch ($sortBy) {
            case 'conversions':
                $query->orderByDesc('cart_conversion_rate');
                break;
            case 'purchases':
                $query->orderByDesc('purchases');
                break;
            default:
                $query->orderByDesc('total_views');
        }

        $productViews = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $productViews,
        ]);
    }

    /**
     * Get checkout funnel analytics.
     */
    public function checkoutFunnel(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $status = $request->input('status');

        $query = CheckoutFunnel::with(['user', 'order', 'visitorSession'])
            ->orderBy('created_at', 'desc');

        if ($dateFrom && $dateTo) {
            $query->dateRange($dateFrom, $dateTo);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $funnels = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $funnels,
        ]);
    }

    /**
     * Get abandoned carts.
     */
    public function abandonedCarts(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $minValue = $request->input('min_value');

        $query = CheckoutFunnel::with(['user', 'visitorSession'])
            ->abandoned()
            ->orderBy('abandoned_at', 'desc');

        if ($dateFrom && $dateTo) {
            $query->dateRange($dateFrom, $dateTo);
        }

        if ($minValue) {
            $query->where('cart_total', '>=', $minValue);
        }

        $abandonedCarts = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $abandonedCarts,
        ]);
    }

    /**
     * Get cart events analytics.
     */
    public function cartEvents(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $eventType = $request->input('event_type');

        $query = CartEvent::with(['product', 'productVariation', 'user'])
            ->orderBy('event_at', 'desc');

        if ($dateFrom && $dateTo) {
            $query->dateRange($dateFrom, $dateTo);
        }

        if ($eventType) {
            $query->eventType($eventType);
        }

        $events = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $events,
        ]);
    }

    /**
     * Get search analytics.
     */
    public function searchAnalytics(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $noResults = $request->input('no_results');

        $query = SearchQuery::select(
            'query',
            DB::raw('COUNT(*) as search_count'),
            DB::raw('AVG(results_count) as avg_results'),
            DB::raw('SUM(CASE WHEN clicked_result = 1 THEN 1 ELSE 0 END) as clicks'),
            DB::raw('(SUM(CASE WHEN clicked_result = 1 THEN 1 ELSE 0 END) / COUNT(*) * 100) as click_rate')
        )
            ->groupBy('query');

        if ($dateFrom && $dateTo) {
            $query->dateRange($dateFrom, $dateTo);
        }

        if ($noResults === 'true' || $noResults === '1') {
            $query->having('avg_results', '=', 0);
        }

        $searches = $query->orderByDesc('search_count')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $searches,
        ]);
    }

    /**
     * Get page views analytics.
     */
    public function pageViews(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $pageType = $request->input('page_type');

        $query = PageView::select(
            'page_type',
            'page_url',
            DB::raw('COUNT(*) as view_count'),
            DB::raw('AVG(time_spent_seconds) as avg_time_spent'),
            DB::raw('COUNT(DISTINCT visitor_session_id) as unique_visitors')
        )
            ->groupBy('page_type', 'page_url');

        if ($dateFrom && $dateTo) {
            $query->dateRange($dateFrom, $dateTo);
        }

        if ($pageType) {
            $query->pageType($pageType);
        }

        $pageViews = $query->orderByDesc('view_count')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $pageViews,
        ]);
    }

    /**
     * Export analytics data.
     */
    public function export(Request $request)
    {
        $type = $request->input('type', 'visitors'); // visitors, products, checkouts, searches
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $filename = "{$type}_analytics_" . now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($type, $dateFrom, $dateTo) {
            $file = fopen('php://output', 'w');

            switch ($type) {
                case 'visitors':
                    $this->exportVisitors($file, $dateFrom, $dateTo);
                    break;
                case 'products':
                    $this->exportProductViews($file, $dateFrom, $dateTo);
                    break;
                case 'checkouts':
                    $this->exportCheckouts($file, $dateFrom, $dateTo);
                    break;
                case 'searches':
                    $this->exportSearches($file, $dateFrom, $dateTo);
                    break;
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // Helper methods for statistics

    protected function getVisitorStats($from, $to)
    {
        $query = VisitorSession::dateRange($from, $to);

        return [
            'total_visitors' => $query->count(),
            'unique_visitors' => $query->distinct('ip_address')->count('ip_address'),
            'authenticated_visitors' => $query->authenticated()->count(),
            'guest_visitors' => $query->guest()->count(),
            'avg_session_duration' => round($query->avg('duration_seconds') / 60, 2), // in minutes
            'by_device' => VisitorSession::dateRange($from, $to)
                ->select('device_type', DB::raw('COUNT(*) as count'))
                ->groupBy('device_type')
                ->pluck('count', 'device_type'),
        ];
    }

    protected function getPageViewStats($from, $to)
    {
        $query = PageView::dateRange($from, $to);

        return [
            'total_page_views' => $query->count(),
            'unique_page_views' => $query->distinct('page_url')->count('page_url'),
            'avg_time_on_page' => round($query->avg('time_spent_seconds'), 2),
            'by_page_type' => PageView::dateRange($from, $to)
                ->select('page_type', DB::raw('COUNT(*) as count'))
                ->groupBy('page_type')
                ->pluck('count', 'page_type'),
        ];
    }

    protected function getProductStats($from, $to)
    {
        $query = ProductView::dateRange($from, $to);

        return [
            'total_product_views' => $query->sum('view_count'),
            'unique_products_viewed' => $query->distinct('product_id')->count('product_id'),
            'avg_time_per_product' => round($query->avg('time_spent_seconds'), 2),
            'view_to_cart_rate' => round($query->where('added_to_cart', true)->count() / max($query->count(), 1) * 100, 2),
            'view_to_purchase_rate' => round($query->where('purchased', true)->count() / max($query->count(), 1) * 100, 2),
            'top_viewed_products' => ProductView::dateRange($from, $to)
                ->select('product_id', DB::raw('SUM(view_count) as total_views'))
                ->with('product:id,name,slug,price')
                ->groupBy('product_id')
                ->orderByDesc('total_views')
                ->limit(10)
                ->get(),
        ];
    }

    protected function getCheckoutFunnelStats($from, $to)
    {
        $query = CheckoutFunnel::dateRange($from, $to);

        $total = $query->count();
        $completed = $query->where('status', 'order_completed')->count();
        $abandoned = $query->where('status', 'abandoned')->count();

        return [
            'total_checkouts' => $total,
            'completed_checkouts' => $completed,
            'abandoned_checkouts' => $abandoned,
            'conversion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
            'abandonment_rate' => $total > 0 ? round(($abandoned / $total) * 100, 2) : 0,
            'avg_cart_value' => round($query->avg('cart_total'), 2),
            'total_abandoned_value' => round($query->where('status', 'abandoned')->sum('cart_total'), 2),
            'by_status' => CheckoutFunnel::dateRange($from, $to)
                ->select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status'),
        ];
    }

    protected function getCartEventStats($from, $to)
    {
        $query = CartEvent::dateRange($from, $to);

        return [
            'total_events' => $query->count(),
            'items_added' => $query->where('event_type', 'added')->sum('quantity'),
            'items_removed' => $query->where('event_type', 'removed')->sum('quantity'),
            'by_event_type' => CartEvent::dateRange($from, $to)
                ->select('event_type', DB::raw('COUNT(*) as count'))
                ->groupBy('event_type')
                ->pluck('count', 'event_type'),
            'most_added_products' => CartEvent::dateRange($from, $to)
                ->where('event_type', 'added')
                ->select('product_id', DB::raw('SUM(quantity) as total_quantity'))
                ->with('product:id,name,slug,price')
                ->groupBy('product_id')
                ->orderByDesc('total_quantity')
                ->limit(10)
                ->get(),
        ];
    }

    protected function getSearchStats($from, $to)
    {
        $query = SearchQuery::dateRange($from, $to);

        return [
            'total_searches' => $query->count(),
            'unique_queries' => $query->distinct('query')->count('query'),
            'avg_results' => round($query->avg('results_count'), 2),
            'no_results_count' => $query->where('results_count', 0)->count(),
            'click_through_rate' => round($query->where('clicked_result', true)->count() / max($query->count(), 1) * 100, 2),
            'top_searches' => SearchQuery::dateRange($from, $to)
                ->select('query', DB::raw('COUNT(*) as count'))
                ->groupBy('query')
                ->orderByDesc('count')
                ->limit(10)
                ->get(),
        ];
    }

    // Export helper methods

    protected function exportVisitors($file, $from, $to)
    {
        fputcsv($file, ['Session ID', 'User', 'Device', 'Browser', 'Platform', 'Page Views', 'Duration (min)', 'First Visit', 'Last Activity']);

        $query = VisitorSession::with('user')->dateRange($from, $to)->orderBy('first_visit_at', 'desc');

        $query->chunk(100, function ($sessions) use ($file) {
            foreach ($sessions as $session) {
                fputcsv($file, [
                    $session->session_id,
                    $session->user ? $session->user->name : 'Guest',
                    $session->device_type,
                    $session->browser,
                    $session->platform,
                    $session->page_views,
                    round($session->duration_seconds / 60, 2),
                    $session->first_visit_at->format('Y-m-d H:i:s'),
                    $session->last_activity_at->format('Y-m-d H:i:s'),
                ]);
            }
        });
    }

    protected function exportProductViews($file, $from, $to)
    {
        fputcsv($file, ['Product', 'SKU', 'Total Views', 'Unique Sessions', 'Cart Additions', 'Purchases', 'Cart Conversion %', 'Purchase Conversion %']);

        $query = ProductView::select(
            'product_id',
            DB::raw('COUNT(*) as total_sessions'),
            DB::raw('SUM(view_count) as total_views'),
            DB::raw('SUM(CASE WHEN added_to_cart = 1 THEN 1 ELSE 0 END) as cart_additions'),
            DB::raw('SUM(CASE WHEN purchased = 1 THEN 1 ELSE 0 END) as purchases'),
            DB::raw('(SUM(CASE WHEN added_to_cart = 1 THEN 1 ELSE 0 END) / COUNT(*) * 100) as cart_conversion_rate'),
            DB::raw('(SUM(CASE WHEN purchased = 1 THEN 1 ELSE 0 END) / COUNT(*) * 100) as purchase_conversion_rate')
        )
            ->with('product:id,name,sku')
            ->groupBy('product_id')
            ->dateRange($from, $to)
            ->orderByDesc('total_views');

        foreach ($query->get() as $view) {
            fputcsv($file, [
                $view->product->name ?? 'N/A',
                $view->product->sku ?? 'N/A',
                $view->total_views,
                $view->total_sessions,
                $view->cart_additions,
                $view->purchases,
                round($view->cart_conversion_rate, 2),
                round($view->purchase_conversion_rate, 2),
            ]);
        }
    }

    protected function exportCheckouts($file, $from, $to)
    {
        fputcsv($file, ['ID', 'User', 'Status', 'Items Count', 'Cart Total', 'Created At', 'Completed/Abandoned At']);

        $query = CheckoutFunnel::with('user')->dateRange($from, $to)->orderBy('created_at', 'desc');

        $query->chunk(100, function ($funnels) use ($file) {
            foreach ($funnels as $funnel) {
                fputcsv($file, [
                    $funnel->id,
                    $funnel->user ? $funnel->user->name : 'Guest',
                    $funnel->status,
                    $funnel->items_count,
                    $funnel->cart_total,
                    $funnel->created_at->format('Y-m-d H:i:s'),
                    $funnel->completed_at ? $funnel->completed_at->format('Y-m-d H:i:s') : ($funnel->abandoned_at ? $funnel->abandoned_at->format('Y-m-d H:i:s') : 'N/A'),
                ]);
            }
        });
    }

    protected function exportSearches($file, $from, $to)
    {
        fputcsv($file, ['Query', 'Search Count', 'Avg Results', 'Clicks', 'Click Rate %']);

        $query = SearchQuery::select(
            'query',
            DB::raw('COUNT(*) as search_count'),
            DB::raw('AVG(results_count) as avg_results'),
            DB::raw('SUM(CASE WHEN clicked_result = 1 THEN 1 ELSE 0 END) as clicks'),
            DB::raw('(SUM(CASE WHEN clicked_result = 1 THEN 1 ELSE 0 END) / COUNT(*) * 100) as click_rate')
        )
            ->groupBy('query')
            ->dateRange($from, $to)
            ->orderByDesc('search_count');

        foreach ($query->get() as $search) {
            fputcsv($file, [
                $search->query,
                $search->search_count,
                round($search->avg_results, 2),
                $search->clicks,
                round($search->click_rate, 2),
            ]);
        }
    }
}
