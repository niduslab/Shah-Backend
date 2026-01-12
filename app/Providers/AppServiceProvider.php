<?php

namespace App\Providers;

use App\Services\Contracts\CampaignServiceInterface;
use App\Services\Contracts\CatalogServiceInterface;
use App\Services\Contracts\CouponServiceInterface;
use App\Services\Contracts\InventoryServiceInterface;
use App\Services\Contracts\InvoiceServiceInterface;
use App\Services\Contracts\OrderServiceInterface;
use App\Services\Contracts\PaymentServiceInterface;
use App\Services\Contracts\PromotionServiceInterface;
use App\Services\Contracts\RefundServiceInterface;
use App\Services\Contracts\ReturnServiceInterface;
use App\Services\Contracts\ReviewServiceInterface;
use App\Services\Contracts\ShippingServiceInterface;
use App\Services\Contracts\UserServiceInterface;
use App\Services\Contracts\VariationServiceInterface;
use App\Services\CampaignService;
use App\Services\CatalogService;
use App\Services\CouponService;
use App\Services\InventoryService;
use App\Services\InvoiceService;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\PromotionService;
use App\Services\RefundService;
use App\Services\ReturnService;
use App\Services\ReviewService;
use App\Services\ShippingService;
use App\Services\UserService;
use App\Services\VariationService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // User & Authentication
        $this->app->bind(UserServiceInterface::class, UserService::class);

        // Catalog & Products
        $this->app->bind(CatalogServiceInterface::class, CatalogService::class);
        $this->app->bind(VariationServiceInterface::class, VariationService::class);

        // Inventory
        $this->app->bind(InventoryServiceInterface::class, InventoryService::class);

        // Shipping
        $this->app->bind(ShippingServiceInterface::class, ShippingService::class);

        // Promotions & Coupons
        $this->app->bind(PromotionServiceInterface::class, PromotionService::class);
        $this->app->bind(CouponServiceInterface::class, CouponService::class);

        // Orders
        $this->app->bind(OrderServiceInterface::class, OrderService::class);

        // Payments & Invoices
        $this->app->bind(PaymentServiceInterface::class, PaymentService::class);
        $this->app->bind(InvoiceServiceInterface::class, InvoiceService::class);

        // Returns & Refunds
        $this->app->bind(ReturnServiceInterface::class, ReturnService::class);
        $this->app->bind(RefundServiceInterface::class, RefundService::class);

        // Reviews
        $this->app->bind(ReviewServiceInterface::class, ReviewService::class);

        // Campaigns
        $this->app->bind(CampaignServiceInterface::class, CampaignService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
