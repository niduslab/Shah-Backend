<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class AnalyticsTrackingController extends Controller
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Track page view.
     */
    public function trackPageView(Request $request)
    {
        $request->validate([
            'page_type' => 'required|string',
            'page_title' => 'nullable|string',
            'product_id' => 'nullable|integer|exists:products,id',
            'category_id' => 'nullable|integer|exists:categories,id',
        ]);

        $this->analyticsService->trackPageView(
            $request,
            $request->input('page_type'),
            $request->input('product_id'),
            $request->input('category_id')
        );

        return response()->json([
            'success' => true,
            'message' => 'Page view tracked',
        ]);
    }

    /**
     * Track product view.
     */
    public function trackProductView(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
        ]);

        $this->analyticsService->trackProductView(
            $request,
            $request->input('product_id')
        );

        return response()->json([
            'success' => true,
            'message' => 'Product view tracked',
        ]);
    }

    /**
     * Track cart event.
     */
    public function trackCartEvent(Request $request)
    {
        $request->validate([
            'event_type' => 'required|in:added,updated,removed',
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'variation_id' => 'nullable|integer|exists:product_variations,id',
        ]);

        $this->analyticsService->trackCartEvent(
            $request,
            $request->input('event_type'),
            $request->input('product_id'),
            $request->input('quantity'),
            $request->input('price'),
            $request->input('variation_id')
        );

        return response()->json([
            'success' => true,
            'message' => 'Cart event tracked',
        ]);
    }

    /**
     * Track checkout funnel stage.
     */
    public function trackCheckout(Request $request)
    {
        $request->validate([
            'status' => 'required|in:cart_viewed,checkout_initiated,shipping_info_entered,payment_info_entered,order_completed,abandoned',
            'cart_items' => 'nullable|array',
            'cart_total' => 'nullable|numeric|min:0',
            'items_count' => 'nullable|integer|min:0',
            'order_id' => 'nullable|integer|exists:orders,id',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'integer|exists:products,id',
            'reason' => 'nullable|string',
        ]);

        $cartData = [];
        if ($request->has('cart_items')) {
            $cartData['items'] = $request->input('cart_items');
        }
        if ($request->has('cart_total')) {
            $cartData['total'] = $request->input('cart_total');
        }
        if ($request->has('items_count')) {
            $cartData['items_count'] = $request->input('items_count');
        }
        if ($request->has('order_id')) {
            $cartData['order_id'] = $request->input('order_id');
        }
        if ($request->has('product_ids')) {
            $cartData['product_ids'] = $request->input('product_ids');
        }
        if ($request->has('reason')) {
            $cartData['reason'] = $request->input('reason');
        }

        $this->analyticsService->trackCheckoutFunnel(
            $request,
            $request->input('status'),
            $cartData
        );

        return response()->json([
            'success' => true,
            'message' => 'Checkout stage tracked',
        ]);
    }

    /**
     * Track search query.
     */
    public function trackSearch(Request $request)
    {
        $request->validate([
            'query' => 'required|string',
            'results_count' => 'required|integer|min:0',
            'clicked_product_id' => 'nullable|integer|exists:products,id',
        ]);

        $this->analyticsService->trackSearch(
            $request,
            $request->input('query'),
            $request->input('results_count'),
            $request->input('clicked_product_id')
        );

        return response()->json([
            'success' => true,
            'message' => 'Search tracked',
        ]);
    }
}
