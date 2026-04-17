<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Services\Contracts\InvoiceServiceInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendInvoiceEmailJob implements ShouldQueue
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
    public $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Invoice $invoice
    ) {}

    /**
     * Execute the job.
     */
    public function handle(InvoiceServiceInterface $invoiceService): void
    {
        try {
            $result = $invoiceService->sendInvoiceEmail($this->invoice);

            if ($result) {
                Log::info("Invoice email sent successfully", [
                    'invoice_number' => $this->invoice->invoice_number,
                    'order_number' => $this->invoice->order->order_number,
                ]);
            } else {
                Log::warning("Invoice email could not be sent", [
                    'invoice_number' => $this->invoice->invoice_number,
                ]);
            }

        } catch (\Exception $e) {
            Log::error("Failed to send invoice email", [
                'invoice_number' => $this->invoice->invoice_number,
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
        Log::error("Invoice email job failed", [
            'invoice_number' => $this->invoice->invoice_number,
            'error' => $exception->getMessage(),
        ]);
    }
}
