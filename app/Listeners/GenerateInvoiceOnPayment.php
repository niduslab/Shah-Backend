<?php

namespace App\Listeners;

use App\Events\PaymentCompleted;
use App\Jobs\GenerateInvoiceJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class GenerateInvoiceOnPayment implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(PaymentCompleted $event): void
    {
        $order = $event->order;

        // Dispatch job to generate invoice if not already exists
        if (!$order->invoice) {
            GenerateInvoiceJob::dispatch($order);
        }
    }
}
