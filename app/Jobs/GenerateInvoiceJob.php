<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\Contracts\InvoiceServiceInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Order $order
    ) {}

    /**
     * Execute the job.
     */
    public function handle(InvoiceServiceInterface $invoiceService): void
    {
        try {
            // Check if invoice already exists
            if ($this->order->invoice) {
                Log::info("Invoice already exists for order {$this->order->order_number}");
                return;
            }

            // Generate the invoice
            $invoice = $invoiceService->generateInvoice($this->order);

            Log::info("Invoice generated successfully for order {$this->order->order_number}", [
                'invoice_number' => $invoice->invoice_number,
            ]);

            // Dispatch job to send invoice email
            SendInvoiceEmailJob::dispatch($invoice);

        } catch (\Exception $e) {
            Log::error("Failed to generate invoice for order {$this->order->order_number}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Invoice generation job failed for order {$this->order->order_number}", [
            'error' => $exception->getMessage(),
        ]);
    }
}
