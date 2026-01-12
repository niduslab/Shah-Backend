<?php

namespace App\Services\Contracts;

use App\Models\Invoice;
use App\Models\Order;

interface InvoiceServiceInterface
{
    /**
     * Generate invoice for an order.
     */
    public function generateInvoice(Order $order): Invoice;

    /**
     * Send invoice via email.
     */
    public function sendInvoiceEmail(Invoice $invoice): bool;

    /**
     * Regenerate invoice PDF.
     */
    public function regenerateInvoice(Invoice $invoice): Invoice;

    /**
     * Get invoice by number.
     */
    public function getInvoiceByNumber(string $invoiceNumber): ?Invoice;
}
