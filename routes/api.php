<?php

use Illuminate\Support\Facades\Route;

// Public Routes
Route::prefix('auth')->group(function () {
    Route::post('register', [\App\Http\Controllers\Api\AuthController::class, 'register']);
    Route::post('login', [\App\Http\Controllers\Api\AuthController::class, 'login']);
    Route::post('google/callback', [\App\Http\Controllers\Api\AuthController::class, 'googleCallback']);
    Route::post('forgot-password', [\App\Http\Controllers\Api\AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [\App\Http\Controllers\Api\AuthController::class, 'resetPassword']);
    // OTP-based password reset
    Route::post('send-otp', [\App\Http\Controllers\Api\AuthController::class, 'sendOtp']);
    Route::post('send-registration-otp', [\App\Http\Controllers\Api\AuthController::class, 'sendRegistrationOtp']);
    Route::post('verify-otp', [\App\Http\Controllers\Api\AuthController::class, 'verifyOtp']);
    Route::post('reset-password-otp', [\App\Http\Controllers\Api\AuthController::class, 'resetPasswordWithOtp']);
    Route::get('csrf-token', [\App\Http\Controllers\Api\AuthController::class, 'csrfToken']);
});

// Catalog (Public)
Route::prefix('catalog')->group(function () {
    Route::get('categories', [\App\Http\Controllers\Api\CatalogController::class, 'categories']);
    Route::get('categories/{slug}', [\App\Http\Controllers\Api\CatalogController::class, 'category']);
    Route::get('categories/{slug}/products', [\App\Http\Controllers\Api\CatalogController::class, 'productsByCategory']);
    Route::get('brands', [\App\Http\Controllers\Api\CatalogController::class, 'brands']);
    Route::get('brands/{slug}/products', [\App\Http\Controllers\Api\CatalogController::class, 'productsByBrand']);
    Route::get('products', [\App\Http\Controllers\Api\CatalogController::class, 'products']);
    Route::get('products/featured', [\App\Http\Controllers\Api\CatalogController::class, 'featured']);
    Route::get('products/trending', [\App\Http\Controllers\Api\CatalogController::class, 'trending']);
    Route::get('products/{slug}', [\App\Http\Controllers\Api\CatalogController::class, 'product']);
});

// Flash Deals (Public)
Route::prefix('flash-deals')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\FlashDealController::class, 'index']);
    Route::get('upcoming', [\App\Http\Controllers\Api\FlashDealController::class, 'upcoming']);
    Route::get('{id}', [\App\Http\Controllers\Api\FlashDealController::class, 'show']);
});

// Galleries (Public)
Route::prefix('galleries')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\GalleryController::class, 'index']);
    Route::get('{slug}', [\App\Http\Controllers\Api\GalleryController::class, 'show']);
});

// Cart (Public)
Route::prefix('cart')->group(function () {
    Route::post('summary', [\App\Http\Controllers\Api\CartController::class, 'summary']);
    Route::post('validate-coupon', [\App\Http\Controllers\Api\CartController::class, 'validateCoupon']);
    Route::post('check-availability', [\App\Http\Controllers\Api\CartController::class, 'checkAvailability']);
    Route::get('available-coupons', [\App\Http\Controllers\Api\CartController::class, 'getAvailableCoupons']);
});

// Reviews (Public - Read)
Route::get('products/{productId}/reviews', [\App\Http\Controllers\Api\ReviewController::class, 'productReviews']);
Route::get('reviews/product/{productId}', [\App\Http\Controllers\Api\ReviewController::class, 'productReviews']);

// Pages (Public)
Route::get('policies', [\App\Http\Controllers\Api\PageController::class, 'allPolicies']);
Route::get('policies/{type}', [\App\Http\Controllers\Api\PageController::class, 'policy']);
Route::get('pages/{slug}', [\App\Http\Controllers\Api\PageController::class, 'show']);
Route::get('pages/type/{type}', [\App\Http\Controllers\Api\PageController::class, 'getByType']);
Route::get('banners/{position?}', [\App\Http\Controllers\Api\PageController::class, 'banners']);

// Page Content (Public - Frontend)
Route::prefix('page-content')->group(function () {
    Route::get('{pageKey}', [\App\Http\Controllers\Api\PageContentController::class, 'getByPageKey']);
    Route::get('brand/{brandSlug}', [\App\Http\Controllers\Api\PageContentController::class, 'getByBrandSlug']);
});

// Order Tracking (Public)
Route::get('orders/{orderNumber}/track', [\App\Http\Controllers\Api\OrderController::class, 'track']);
Route::get('orders/{orderNumber}', [\App\Http\Controllers\Api\OrderController::class, 'show']);

// Visitor Popup (Public)
Route::post('visitor-popup', [\App\Http\Controllers\Api\VisitorPopupController::class, 'store']);

// Analytics Tracking (Public)
Route::prefix('analytics')->group(function () {
    Route::post('track/page-view', [\App\Http\Controllers\Api\AnalyticsTrackingController::class, 'trackPageView']);
    Route::post('track/product-view', [\App\Http\Controllers\Api\AnalyticsTrackingController::class, 'trackProductView']);
    Route::post('track/cart-event', [\App\Http\Controllers\Api\AnalyticsTrackingController::class, 'trackCartEvent']);
    Route::post('track/checkout', [\App\Http\Controllers\Api\AnalyticsTrackingController::class, 'trackCheckout']);
    Route::post('track/search', [\App\Http\Controllers\Api\AnalyticsTrackingController::class, 'trackSearch']);
});

// Checkout (Public - supports both guest and authenticated)
Route::prefix('checkout')->group(function () {
    Route::post('shipping-methods', [\App\Http\Controllers\Api\CheckoutController::class, 'shippingMethods']);
    Route::post('preview', [\App\Http\Controllers\Api\CheckoutController::class, 'preview']);
    Route::post('process', [\App\Http\Controllers\Api\CheckoutController::class, 'process']);
});

// Payment Callbacks (Public - No Auth)
Route::prefix('payments')->group(function () {
    Route::post('ssl-commerz/ipn', [\App\Http\Controllers\Api\PaymentController::class, 'sslCommerzIpn'])->name('payment.ipn');
    Route::match(['get', 'post'], 'ssl-commerz/success', [\App\Http\Controllers\Api\PaymentController::class, 'sslCommerzSuccess'])->name('payment.success');
    Route::match(['get', 'post'], 'ssl-commerz/fail', [\App\Http\Controllers\Api\PaymentController::class, 'sslCommerzFail'])->name('payment.fail');
    Route::match(['get', 'post'], 'ssl-commerz/cancel', [\App\Http\Controllers\Api\PaymentController::class, 'sslCommerzCancel'])->name('payment.cancel');
    Route::get('{orderNumber}/status', [\App\Http\Controllers\Api\PaymentController::class, 'status']);
});

// Authenticated Customer Routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('auth/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);
    Route::get('auth/user', [\App\Http\Controllers\Api\AuthController::class, 'user']);
    Route::put('auth/profile', [\App\Http\Controllers\Api\AuthController::class, 'updateProfile']);
    Route::put('auth/password', [\App\Http\Controllers\Api\AuthController::class, 'changePassword']);

    // Orders
    Route::prefix('orders')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\OrderController::class, 'index']);
        Route::post('{orderNumber}/cancel', [\App\Http\Controllers\Api\OrderController::class, 'cancel']);
        Route::get('{orderNumber}/invoice', [\App\Http\Controllers\Api\OrderController::class, 'invoice']);
    });

    // Payments
    Route::post('payments/{orderNumber}/retry', [\App\Http\Controllers\Api\PaymentController::class, 'retry']);
    Route::post('payments/{orderNumber}/pay-preorder-balance', [\App\Http\Controllers\Api\PaymentController::class, 'payPreorderBalance']);

    // Reviews
    Route::prefix('reviews')->group(function () {
        Route::get('my-reviews', [\App\Http\Controllers\Api\ReviewController::class, 'myReviews']);
        Route::get('reviewable-items', [\App\Http\Controllers\Api\ReviewController::class, 'reviewableItems']);
        Route::get('order/{orderNumber}', [\App\Http\Controllers\Api\ReviewController::class, 'orderReviews']);
        Route::post('/', [\App\Http\Controllers\Api\ReviewController::class, 'store']);
        Route::post('{reviewId}/helpful', [\App\Http\Controllers\Api\ReviewController::class, 'markHelpful']);
    });

    // Returns
    Route::prefix('returns')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\ReturnController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Api\ReturnController::class, 'store']);
        Route::get('{id}', [\App\Http\Controllers\Api\ReturnController::class, 'show']);
    });

    // User Dashboard
    Route::get('dashboard', [\App\Http\Controllers\Api\UserDashboardController::class, 'index']);
    Route::get('profile', [\App\Http\Controllers\Api\UserDashboardController::class, 'profile']);

    // Addresses
    Route::prefix('addresses')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\AddressController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Api\AddressController::class, 'store']);
        Route::get('{id}', [\App\Http\Controllers\Api\AddressController::class, 'show']);
        Route::put('{id}', [\App\Http\Controllers\Api\AddressController::class, 'update']);
        Route::delete('{id}', [\App\Http\Controllers\Api\AddressController::class, 'destroy']);
        Route::post('{id}/set-default', [\App\Http\Controllers\Api\AddressController::class, 'setDefault']);
    });

    // Wishlist
    Route::prefix('wishlist')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\WishlistController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Api\WishlistController::class, 'store']);
        Route::delete('{id}', [\App\Http\Controllers\Api\WishlistController::class, 'destroy']);
        Route::delete('product/{productId}', [\App\Http\Controllers\Api\WishlistController::class, 'removeByProduct']);
        Route::get('check/{productId}', [\App\Http\Controllers\Api\WishlistController::class, 'check']);
        Route::post('clear', [\App\Http\Controllers\Api\WishlistController::class, 'clear']);
    });

    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\NotificationController::class, 'index']);
        Route::get('unread-count', [\App\Http\Controllers\Api\NotificationController::class, 'unreadCount']);
        Route::post('{id}/mark-as-read', [\App\Http\Controllers\Api\NotificationController::class, 'markAsRead']);
        Route::post('mark-all-as-read', [\App\Http\Controllers\Api\NotificationController::class, 'markAllAsRead']);
        Route::delete('{id}', [\App\Http\Controllers\Api\NotificationController::class, 'destroy']);
        Route::post('clear', [\App\Http\Controllers\Api\NotificationController::class, 'clear']);
    });
});

// Admin Routes
Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    // Dashboard
    Route::get('dashboard', [\App\Http\Controllers\Api\Admin\ReportController::class, 'dashboard']);

    // Users
    Route::apiResource('users', \App\Http\Controllers\Api\Admin\UserController::class);
    Route::post('users/{id}/toggle-status', [\App\Http\Controllers\Api\Admin\UserController::class, 'toggleStatus']);

    // Categories
    Route::get('categories/tree', [\App\Http\Controllers\Api\Admin\CategoryController::class, 'tree']);
    Route::apiResource('categories', \App\Http\Controllers\Api\Admin\CategoryController::class);

    // Product Bulk Import (MUST be before apiResource to avoid route conflicts)
    Route::prefix('products/import')->group(function () {
        Route::get('template', [\App\Http\Controllers\Api\Admin\ProductImportController::class, 'template']);
        Route::post('upload', [\App\Http\Controllers\Api\Admin\ProductImportController::class, 'upload']);
        Route::get('/', [\App\Http\Controllers\Api\Admin\ProductImportController::class, 'index']);
        Route::get('{id}', [\App\Http\Controllers\Api\Admin\ProductImportController::class, 'status']);
        Route::get('{id}/errors', [\App\Http\Controllers\Api\Admin\ProductImportController::class, 'errors']);
        Route::get('{id}/export-errors', [\App\Http\Controllers\Api\Admin\ProductImportController::class, 'exportErrors']);
        Route::post('{id}/cancel', [\App\Http\Controllers\Api\Admin\ProductImportController::class, 'cancel']);
        Route::delete('{id}', [\App\Http\Controllers\Api\Admin\ProductImportController::class, 'destroy']);
    });

    // Products
    Route::apiResource('products', \App\Http\Controllers\Api\Admin\ProductController::class);
    Route::post('products/{id}/variations', [\App\Http\Controllers\Api\Admin\ProductController::class, 'addVariation']);
    Route::put('products/{productId}/variations/{variationId}', [\App\Http\Controllers\Api\Admin\ProductController::class, 'updateVariation']);
    Route::delete('products/{productId}/variations/{variationId}', [\App\Http\Controllers\Api\Admin\ProductController::class, 'deleteVariation']);
    
    // Product Images
    Route::post('products/{id}/images', [\App\Http\Controllers\Api\Admin\ProductController::class, 'addImages']);
    Route::put('products/{productId}/images/{imageId}', [\App\Http\Controllers\Api\Admin\ProductController::class, 'updateImage']);
    Route::delete('products/{productId}/images/{imageId}', [\App\Http\Controllers\Api\Admin\ProductController::class, 'deleteImage']);
    Route::post('products/{productId}/images/{imageId}/set-primary', [\App\Http\Controllers\Api\Admin\ProductController::class, 'setPrimaryImage']);
    Route::post('products/{productId}/images/reorder', [\App\Http\Controllers\Api\Admin\ProductController::class, 'reorderImages']);

    // Variation Types (Size, Color, etc.)
    Route::apiResource('variations', \App\Http\Controllers\Api\Admin\VariationController::class);
    
    // Variation Options (S, M, L, Red, Blue, etc.)
    Route::get('variations/{variationId}/options', [\App\Http\Controllers\Api\Admin\VariationOptionController::class, 'index']);
    Route::post('variations/{variationId}/options', [\App\Http\Controllers\Api\Admin\VariationOptionController::class, 'store']);
    Route::post('variations/{variationId}/options/bulk', [\App\Http\Controllers\Api\Admin\VariationOptionController::class, 'bulkStore']);
    Route::get('variations/{variationId}/options/{optionId}', [\App\Http\Controllers\Api\Admin\VariationOptionController::class, 'show']);
    Route::put('variations/{variationId}/options/{optionId}', [\App\Http\Controllers\Api\Admin\VariationOptionController::class, 'update']);
    Route::delete('variations/{variationId}/options/{optionId}', [\App\Http\Controllers\Api\Admin\VariationOptionController::class, 'destroy']);

    // Brands & Models
    Route::apiResource('brands', \App\Http\Controllers\Api\Admin\BrandController::class);
    Route::apiResource('product-models', \App\Http\Controllers\Api\Admin\ProductModelController::class);

    // Orders
    Route::apiResource('orders', \App\Http\Controllers\Api\Admin\OrderController::class)->only(['index', 'show']);
    Route::get('orders/{orderNumber}/invoice', [\App\Http\Controllers\Api\Admin\OrderController::class, 'invoice']);
    Route::put('orders/{id}/status', [\App\Http\Controllers\Api\Admin\OrderController::class, 'updateStatus']);
    Route::post('orders/{id}/cancel', [\App\Http\Controllers\Api\Admin\OrderController::class, 'cancel']);
    Route::post('orders/{id}/tracking', [\App\Http\Controllers\Api\Admin\OrderController::class, 'assignTracking']);
    Route::put('orders/{id}/notes', [\App\Http\Controllers\Api\Admin\OrderController::class, 'updateNotes']);

    // POS
    Route::prefix('pos')->group(function () {
        Route::post('orders', [\App\Http\Controllers\Api\Admin\POSController::class, 'createOrder']);
        Route::get('products/search', [\App\Http\Controllers\Api\Admin\POSController::class, 'searchProducts']);
        Route::get('products/sku/{sku}', [\App\Http\Controllers\Api\Admin\POSController::class, 'getProductBySku']);
        Route::post('calculate', [\App\Http\Controllers\Api\Admin\POSController::class, 'calculateTotals']);
        Route::post('quotation', [\App\Http\Controllers\Api\Admin\POSController::class, 'generateQuotation']);
    });

    // Promotions
    Route::apiResource('promotions', \App\Http\Controllers\Api\Admin\PromotionController::class);
    Route::post('promotions/{id}/toggle', [\App\Http\Controllers\Api\Admin\PromotionController::class, 'toggleStatus']);

    // Coupons
    Route::apiResource('coupons', \App\Http\Controllers\Api\Admin\CouponController::class);
    Route::get('coupons/{id}/usage', [\App\Http\Controllers\Api\Admin\CouponController::class, 'usageHistory']);

    // Shipping
    Route::apiResource('shipping-rates', \App\Http\Controllers\Api\Admin\ShippingController::class);
    Route::get('shipping-classes', [\App\Http\Controllers\Api\Admin\ShippingController::class, 'classes']);
    Route::post('shipping-classes', [\App\Http\Controllers\Api\Admin\ShippingController::class, 'storeClass']);
    Route::put('shipping-classes/{id}', [\App\Http\Controllers\Api\Admin\ShippingController::class, 'updateClass']);
    Route::delete('shipping-classes/{id}', [\App\Http\Controllers\Api\Admin\ShippingController::class, 'destroyClass']);

    // Inventory
    Route::prefix('inventory')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\Admin\InventoryController::class, 'index']);
        Route::get('low-stock', [\App\Http\Controllers\Api\Admin\InventoryController::class, 'lowStock']);
        Route::get('logs', [\App\Http\Controllers\Api\Admin\InventoryController::class, 'logs']);
        Route::get('{productId}', [\App\Http\Controllers\Api\Admin\InventoryController::class, 'show']);
        Route::post('{productId}/adjust', [\App\Http\Controllers\Api\Admin\InventoryController::class, 'adjust']);
        Route::post('bulk-adjust', [\App\Http\Controllers\Api\Admin\InventoryController::class, 'bulkAdjust']);
    });

    // Returns
    Route::prefix('returns')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\Admin\ReturnController::class, 'index']);
        Route::get('{id}', [\App\Http\Controllers\Api\Admin\ReturnController::class, 'show']);
        Route::post('{id}/approve', [\App\Http\Controllers\Api\Admin\ReturnController::class, 'approve']);
        Route::post('{id}/reject', [\App\Http\Controllers\Api\Admin\ReturnController::class, 'reject']);
        Route::post('{id}/process', [\App\Http\Controllers\Api\Admin\ReturnController::class, 'process']);
    });

    // Refunds
    Route::prefix('refunds')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\Admin\RefundController::class, 'index']);
        Route::get('{id}', [\App\Http\Controllers\Api\Admin\RefundController::class, 'show']);
        Route::post('/', [\App\Http\Controllers\Api\Admin\RefundController::class, 'store']);
        Route::post('{id}/process', [\App\Http\Controllers\Api\Admin\RefundController::class, 'process']);
        Route::post('{id}/cancel', [\App\Http\Controllers\Api\Admin\RefundController::class, 'cancel']);
    });

    // Reviews
    Route::prefix('reviews')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\Admin\ReviewController::class, 'index']);
        Route::get('statistics', [\App\Http\Controllers\Api\Admin\ReviewController::class, 'statistics']);
        Route::get('{id}', [\App\Http\Controllers\Api\Admin\ReviewController::class, 'show']);
        Route::post('{id}/approve', [\App\Http\Controllers\Api\Admin\ReviewController::class, 'approve']);
        Route::post('{id}/reject', [\App\Http\Controllers\Api\Admin\ReviewController::class, 'reject']);
        Route::post('{id}/respond', [\App\Http\Controllers\Api\Admin\ReviewController::class, 'respond']);
        Route::delete('{id}', [\App\Http\Controllers\Api\Admin\ReviewController::class, 'destroy']);
    });

    // Campaigns
    Route::apiResource('campaigns', \App\Http\Controllers\Api\Admin\CampaignController::class);
    Route::post('campaigns/{id}/send', [\App\Http\Controllers\Api\Admin\CampaignController::class, 'send']);
    Route::get('campaigns/{id}/statistics', [\App\Http\Controllers\Api\Admin\CampaignController::class, 'statistics']);
    Route::post('campaigns/preview-recipients', [\App\Http\Controllers\Api\Admin\CampaignController::class, 'previewRecipients']);

    // Content Management
    Route::prefix('content')->group(function () {
        // Policies
        Route::get('policies', [\App\Http\Controllers\Api\Admin\ContentController::class, 'policies']);
        Route::get('policies/{id}', [\App\Http\Controllers\Api\Admin\ContentController::class, 'showPolicy']);
        Route::post('policies', [\App\Http\Controllers\Api\Admin\ContentController::class, 'storePolicy']);
        Route::put('policies/{id}', [\App\Http\Controllers\Api\Admin\ContentController::class, 'updatePolicy']);
        // Pages
        Route::get('pages', [\App\Http\Controllers\Api\Admin\ContentController::class, 'pages']);
        Route::get('pages/{id}', [\App\Http\Controllers\Api\Admin\ContentController::class, 'showPage']);
        Route::post('pages', [\App\Http\Controllers\Api\Admin\ContentController::class, 'storePage']);
        Route::put('pages/{id}', [\App\Http\Controllers\Api\Admin\ContentController::class, 'updatePage']);
        Route::delete('pages/{id}', [\App\Http\Controllers\Api\Admin\ContentController::class, 'destroyPage']);
        // Banners
        Route::get('banners', [\App\Http\Controllers\Api\Admin\ContentController::class, 'banners']);
        Route::post('banners', [\App\Http\Controllers\Api\Admin\ContentController::class, 'storeBanner']);
        Route::put('banners/{id}', [\App\Http\Controllers\Api\Admin\ContentController::class, 'updateBanner']);
        Route::delete('banners/{id}', [\App\Http\Controllers\Api\Admin\ContentController::class, 'destroyBanner']);
    });

    // Reports
    Route::prefix('reports')->group(function () {
        Route::get('sales', [\App\Http\Controllers\Api\Admin\ReportController::class, 'sales']);
        Route::get('products', [\App\Http\Controllers\Api\Admin\ReportController::class, 'products']);
        Route::get('customers', [\App\Http\Controllers\Api\Admin\ReportController::class, 'customers']);
        Route::get('inventory', [\App\Http\Controllers\Api\Admin\ReportController::class, 'inventory']);
        Route::get('order-status', [\App\Http\Controllers\Api\Admin\ReportController::class, 'orderStatus']);
    });

    // Flash Deals
    Route::apiResource('flash-deals', \App\Http\Controllers\Api\Admin\FlashDealController::class);
    Route::post('flash-deals/{id}/toggle', [\App\Http\Controllers\Api\Admin\FlashDealController::class, 'toggleStatus']);
    Route::get('flash-deals/{id}/statistics', [\App\Http\Controllers\Api\Admin\FlashDealController::class, 'statistics']);

    // Galleries
    Route::apiResource('galleries', \App\Http\Controllers\Api\Admin\GalleryController::class);
    Route::post('galleries/{id}/images', [\App\Http\Controllers\Api\Admin\GalleryController::class, 'addImage']);
    Route::put('galleries/{galleryId}/images/{imageId}', [\App\Http\Controllers\Api\Admin\GalleryController::class, 'updateImage']);
    Route::delete('galleries/{galleryId}/images/{imageId}', [\App\Http\Controllers\Api\Admin\GalleryController::class, 'deleteImage']);

    // Dynamic Pages Management
    Route::get('page-templates', [\App\Http\Controllers\Api\Admin\PageController::class, 'getTemplates']);
    Route::get('page-templates/category/{category}', [\App\Http\Controllers\Api\Admin\PageController::class, 'getTemplatesByCategory']);
    Route::get('page-templates/page-type/{pageType}', [\App\Http\Controllers\Api\Admin\PageController::class, 'getTemplatesByPageType']);
    Route::get('page-templates/{templateType}/schema', [\App\Http\Controllers\Api\Admin\PageController::class, 'getTemplateSchema']);
    
    Route::apiResource('pages', \App\Http\Controllers\Api\Admin\PageController::class);
    Route::post('pages/{page}/duplicate', [\App\Http\Controllers\Api\Admin\PageController::class, 'duplicate']);
    Route::get('pages/{page}/sections', [\App\Http\Controllers\Api\Admin\PageController::class, 'getSections']);
    Route::post('pages/{page}/sections', [\App\Http\Controllers\Api\Admin\PageController::class, 'addSection']);
    Route::get('pages/{page}/sections/{section}', [\App\Http\Controllers\Api\Admin\PageController::class, 'getSection']);
    Route::put('pages/{page}/sections/{section}', [\App\Http\Controllers\Api\Admin\PageController::class, 'updateSection']);
    Route::delete('pages/{page}/sections/{section}', [\App\Http\Controllers\Api\Admin\PageController::class, 'deleteSection']);
    Route::post('pages/{page}/sections/reorder', [\App\Http\Controllers\Api\Admin\PageController::class, 'reorderSections']);

    // Media Library
    Route::apiResource('media', \App\Http\Controllers\Api\Admin\MediaLibraryController::class);

    // Notifications (Admin)
    Route::prefix('notifications')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\Admin\NotificationController::class, 'index']);
        Route::get('unread-count', [\App\Http\Controllers\Api\Admin\NotificationController::class, 'unreadCount']);
        Route::post('{id}/mark-as-read', [\App\Http\Controllers\Api\Admin\NotificationController::class, 'markAsRead']);
        Route::post('mark-all-as-read', [\App\Http\Controllers\Api\Admin\NotificationController::class, 'markAllAsRead']);
        Route::delete('{id}', [\App\Http\Controllers\Api\Admin\NotificationController::class, 'destroy']);
        Route::post('clear', [\App\Http\Controllers\Api\Admin\NotificationController::class, 'clear']);
    });

    // Page Content Management (Admin)
    Route::prefix('page-contents')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\Admin\PageContentController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Api\Admin\PageContentController::class, 'store']);
        Route::get('{id}', [\App\Http\Controllers\Api\Admin\PageContentController::class, 'show']);
        Route::put('{id}', [\App\Http\Controllers\Api\Admin\PageContentController::class, 'update']);
        Route::delete('{id}', [\App\Http\Controllers\Api\Admin\PageContentController::class, 'destroy']);
        Route::get('page/{pageKey}', [\App\Http\Controllers\Api\Admin\PageContentController::class, 'getByPageKey']);
        Route::post('sort-order', [\App\Http\Controllers\Api\Admin\PageContentController::class, 'updateSortOrder']);
    });

    // Visitor Popup Management (Admin)
    Route::prefix('visitor-popups')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\Admin\VisitorPopupController::class, 'index']);
        Route::get('statistics', [\App\Http\Controllers\Api\Admin\VisitorPopupController::class, 'statistics']);
        Route::get('export', [\App\Http\Controllers\Api\Admin\VisitorPopupController::class, 'export']);
        Route::get('{id}', [\App\Http\Controllers\Api\Admin\VisitorPopupController::class, 'show']);
        Route::delete('{id}', [\App\Http\Controllers\Api\Admin\VisitorPopupController::class, 'destroy']);
    });

    // Analytics (Admin)
    Route::prefix('analytics')->group(function () {
        Route::get('dashboard', [\App\Http\Controllers\Api\Admin\AnalyticsController::class, 'dashboard']);
        Route::get('visitors', [\App\Http\Controllers\Api\Admin\AnalyticsController::class, 'visitors']);
        Route::get('visitors/{id}', [\App\Http\Controllers\Api\Admin\AnalyticsController::class, 'visitorDetails']);
        Route::get('product-views', [\App\Http\Controllers\Api\Admin\AnalyticsController::class, 'productViews']);
        Route::get('checkout-funnel', [\App\Http\Controllers\Api\Admin\AnalyticsController::class, 'checkoutFunnel']);
        Route::get('abandoned-carts', [\App\Http\Controllers\Api\Admin\AnalyticsController::class, 'abandonedCarts']);
        Route::get('cart-events', [\App\Http\Controllers\Api\Admin\AnalyticsController::class, 'cartEvents']);
        Route::get('search', [\App\Http\Controllers\Api\Admin\AnalyticsController::class, 'searchAnalytics']);
        Route::get('page-views', [\App\Http\Controllers\Api\Admin\AnalyticsController::class, 'pageViews']);
        Route::get('export', [\App\Http\Controllers\Api\Admin\AnalyticsController::class, 'export']);
    });
});
