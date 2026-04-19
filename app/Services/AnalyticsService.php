<?php

namespace App\Services;

use App\Models\CartEvent;
use App\Models\CheckoutFunnel;
use App\Models\PageView;
use App\Models\ProductView;
use App\Models\SearchQuery;
use App\Models\VisitorSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;

class AnalyticsService
{
    protected $agent;

    public function __construct()
    {
        $this->agent = new Agent();
    }

    /**
     * Get or create visitor session.
     */
    public function getOrCreateSession($request): VisitorSession
    {
        $sessionId = $request->session()->getId();
        
        $session = VisitorSession::where('session_id', $sessionId)->first();

        if (!$session) {
            $session = VisitorSession::create([
                'session_id' => $sessionId,
                'user_id' => Auth::id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'device_type' => $this->getDeviceType(),
                'browser' => $this->agent->browser(),
                'platform' => $this->agent->platform(),
                'referrer' => $request->header('referer'),
                'landing_page' => $request->fullUrl(),
                'first_visit_at' => now(),
                'last_activity_at' => now(),
            ]);
        } else {
            // Update last activity and user_id if logged in
            $session->update([
                'user_id' => Auth::id() ?? $session->user_id,
                'last_activity_at' => now(),
            ]);
        }

        return $session;
    }

    /**
     * Track page view.
     */
    public function trackPageView($request, string $pageType, ?int $productId = null, ?int $categoryId = null): void
    {
        $session = $this->getOrCreateSession($request);

        PageView::create([
            'visitor_session_id' => $session->id,
            'user_id' => Auth::id(),
            'page_type' => $pageType,
            'page_url' => $request->fullUrl(),
            'page_title' => $request->input('page_title'),
            'product_id' => $productId,
            'category_id' => $categoryId,
            'viewed_at' => now(),
        ]);

        // Update session page views count
        $session->increment('page_views');
    }

    /**
     * Track product view.
     */
    public function trackProductView($request, int $productId): void
    {
        $session = $this->getOrCreateSession($request);

        $productView = ProductView::where('visitor_session_id', $session->id)
            ->where('product_id', $productId)
            ->first();

        if ($productView) {
            $productView->update([
                'view_count' => $productView->view_count + 1,
                'last_viewed_at' => now(),
            ]);
        } else {
            ProductView::create([
                'product_id' => $productId,
                'visitor_session_id' => $session->id,
                'user_id' => Auth::id(),
                'view_count' => 1,
                'first_viewed_at' => now(),
                'last_viewed_at' => now(),
            ]);
        }

        // Also track as page view
        $this->trackPageView($request, 'product', $productId);
    }

    /**
     * Track cart event (add, update, remove).
     */
    public function trackCartEvent($request, string $eventType, int $productId, int $quantity, float $price, ?int $variationId = null): void
    {
        $session = $this->getOrCreateSession($request);

        CartEvent::create([
            'visitor_session_id' => $session->id,
            'user_id' => Auth::id(),
            'product_id' => $productId,
            'product_variation_id' => $variationId,
            'event_type' => $eventType,
            'quantity' => $quantity,
            'price' => $price,
            'event_at' => now(),
        ]);

        // Update product view if exists
        if ($eventType === 'added') {
            ProductView::where('visitor_session_id', $session->id)
                ->where('product_id', $productId)
                ->update(['added_to_cart' => true]);
        }
    }

    /**
     * Track checkout funnel stage.
     */
    public function trackCheckoutFunnel($request, string $status, array $cartData = []): CheckoutFunnel
    {
        $session = $this->getOrCreateSession($request);

        $funnel = CheckoutFunnel::where('visitor_session_id', $session->id)
            ->whereIn('status', ['cart_viewed', 'checkout_initiated', 'shipping_info_entered', 'payment_info_entered'])
            ->latest()
            ->first();

        $updateData = [
            'status' => $status,
            'user_id' => Auth::id() ?? ($funnel->user_id ?? null),
        ];

        // Set timestamp based on status
        switch ($status) {
            case 'cart_viewed':
                $updateData['cart_viewed_at'] = now();
                break;
            case 'checkout_initiated':
                $updateData['checkout_initiated_at'] = now();
                break;
            case 'shipping_info_entered':
                $updateData['shipping_entered_at'] = now();
                break;
            case 'payment_info_entered':
                $updateData['payment_entered_at'] = now();
                break;
            case 'order_completed':
                $updateData['completed_at'] = now();
                $updateData['order_id'] = $cartData['order_id'] ?? null;
                break;
            case 'abandoned':
                $updateData['abandoned_at'] = now();
                $updateData['abandonment_reason'] = $cartData['reason'] ?? 'unknown';
                break;
        }

        // Update cart data if provided
        if (!empty($cartData)) {
            if (isset($cartData['items'])) {
                $updateData['cart_items'] = $cartData['items'];
            }
            if (isset($cartData['total'])) {
                $updateData['cart_total'] = $cartData['total'];
            }
            if (isset($cartData['items_count'])) {
                $updateData['items_count'] = $cartData['items_count'];
            }
        }

        if ($funnel) {
            $funnel->update($updateData);
        } else {
            $updateData['visitor_session_id'] = $session->id;
            $funnel = CheckoutFunnel::create($updateData);
        }

        // Mark products as purchased if order completed
        if ($status === 'order_completed' && isset($cartData['product_ids'])) {
            ProductView::where('visitor_session_id', $session->id)
                ->whereIn('product_id', $cartData['product_ids'])
                ->update(['purchased' => true]);
        }

        return $funnel;
    }

    /**
     * Track search query.
     */
    public function trackSearch($request, string $query, int $resultsCount, ?int $clickedProductId = null): void
    {
        $session = $this->getOrCreateSession($request);

        SearchQuery::create([
            'visitor_session_id' => $session->id,
            'user_id' => Auth::id(),
            'query' => $query,
            'results_count' => $resultsCount,
            'clicked_result' => $clickedProductId !== null,
            'clicked_product_id' => $clickedProductId,
            'searched_at' => now(),
        ]);
    }

    /**
     * Mark checkout as abandoned (called by scheduled task).
     */
    public function markAbandonedCheckouts(): void
    {
        // Mark checkouts as abandoned if no activity for 30 minutes
        CheckoutFunnel::whereIn('status', ['cart_viewed', 'checkout_initiated', 'shipping_info_entered', 'payment_info_entered'])
            ->whereHas('visitorSession', function ($query) {
                $query->where('last_activity_at', '<', now()->subMinutes(30));
            })
            ->update([
                'status' => 'abandoned',
                'abandoned_at' => now(),
                'abandonment_reason' => 'timeout',
            ]);
    }

    /**
     * Get device type from user agent.
     */
    protected function getDeviceType(): string
    {
        if ($this->agent->isMobile()) {
            return 'mobile';
        } elseif ($this->agent->isTablet()) {
            return 'tablet';
        } else {
            return 'desktop';
        }
    }

    /**
     * Update session duration.
     */
    public function updateSessionDuration($request): void
    {
        $session = $this->getOrCreateSession($request);
        
        $duration = now()->diffInSeconds($session->first_visit_at);
        $session->update(['duration_seconds' => $duration]);
    }
}
