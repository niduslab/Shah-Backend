<?php

namespace App\Listeners;

use App\Events\PaymentCompleted;
use App\Services\Contracts\InvoiceServiceInterface;
use Illuminate\Contracts\Queue\ShouldQueue;

class GenerateInvoiceOnPayment implements ShouldQueue
{
    public function __construct(
        protected InvoiceServiceInterface $invoiceService
    ) {}

    public function handle(PaymentCompleted $event): void
    {
        $order = $event->order;

        // Generate invoice if not already exists
        if (!$order->invoice) {
            $invoice = $this->invoiceService->generateInvoice($order);
            $this->invoiceService->sendInvoiceEmail($invoice);
        }
    }
}
