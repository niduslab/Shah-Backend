<?php

namespace App\Providers;

use App\Events\LowStockAlert;
use App\Events\OrderPlaced;
use App\Events\OrderStatusChanged;
use App\Events\PaymentCompleted;
use App\Events\ReturnCompleted;
use App\Listeners\GenerateInvoiceOnPayment;
use App\Listeners\NotifyLowStock;
use App\Listeners\RestoreInventoryOnReturn;
use App\Listeners\SendOrderConfirmation;
use App\Listeners\SendOrderStatusUpdate;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        OrderPlaced::class => [
            SendOrderConfirmation::class,
        ],
        OrderStatusChanged::class => [
            SendOrderStatusUpdate::class,
        ],
        PaymentCompleted::class => [
            GenerateInvoiceOnPayment::class,
        ],
        LowStockAlert::class => [
            NotifyLowStock::class,
        ],
        ReturnCompleted::class => [
            RestoreInventoryOnReturn::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
