<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Order;
use App\Services\Contracts\InvoiceServiceInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class InvoiceService implements InvoiceServiceInterface
{
    /**
     * Generate invoice for an order.
     * 
     * @param Order $order
     * @return Invoice
     */
    public function generateInvoice(Order $order): Invoice
    {
        // Check if invoice already exists
        $existingInvoice = Invoice::where('order_id', $order->id)->first();
        if ($existingInvoice) {
            return $this->regenerateInvoice($existingInvoice);
        }

        // Create invoice record
        $invoice = Invoice::create([
            'order_id' => $order->id,
            'invoice_number' => $this->generateInvoiceNumber(),
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'subtotal' => $order->subtotal,
            'shipping_cost' => $order->shipping_cost,
            'discount_amount' => $order->discount_amount,
            'tax_amount' => $order->tax_amount,
            'total_amount' => $order->total_amount,
        ]);

        // Generate PDF
        $pdfPath = $this->generatePdf($invoice, $order);
        $invoice->update(['pdf_path' => $pdfPath]);

        return $invoice;
    }

    /**
     * Send invoice via email.
     * 
     * @param Invoice $invoice
     * @return bool
     */
    public function sendInvoiceEmail(Invoice $invoice): bool
    {
        $order = $invoice->order;
        $email = $order->customer_email ?? $order->user?->email;

        if (!$email) {
            return false;
        }

        try {
            // Regenerate PDF if not exists
            if (!$invoice->pdf_path || !Storage::exists($invoice->pdf_path)) {
                $this->regenerateInvoice($invoice);
            }

            Mail::send('emails.invoice', [
                'invoice' => $invoice,
                'order' => $order,
            ], function ($message) use ($email, $invoice, $order) {
                $message->to($email)
                    ->subject("Invoice #{$invoice->invoice_number} - Shah Sports")
                    ->attach(Storage::path($invoice->pdf_path), [
                        'as' => "invoice-{$invoice->invoice_number}.pdf",
                        'mime' => 'application/pdf',
                    ]);
            });

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to send invoice email', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Regenerate invoice PDF.
     * 
     * @param Invoice $invoice
     * @return Invoice
     */
    public function regenerateInvoice(Invoice $invoice): Invoice
    {
        $order = $invoice->order->load(['items.product', 'user', 'shippingAddress']);
        
        // Delete old PDF if exists
        if ($invoice->pdf_path && Storage::exists($invoice->pdf_path)) {
            Storage::delete($invoice->pdf_path);
        }

        // Generate new PDF
        $pdfPath = $this->generatePdf($invoice, $order);
        $invoice->update(['pdf_path' => $pdfPath]);

        return $invoice->fresh();
    }

    /**
     * Get invoice by number.
     * 
     * @param string $invoiceNumber
     * @return Invoice|null
     */
    public function getInvoiceByNumber(string $invoiceNumber): ?Invoice
    {
        return Invoice::where('invoice_number', $invoiceNumber)
            ->with(['order.items.product', 'order.user'])
            ->first();
    }

    /**
     * Generate unique invoice number.
     * 
     * @return string
     */
    protected function generateInvoiceNumber(): string
    {
        $year = now()->format('Y');
        $month = now()->format('m');
        
        // Get the last invoice number for this month
        $lastInvoice = Invoice::where('invoice_number', 'like', "INV-{$year}{$month}-%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastInvoice) {
            // Extract the sequence number and increment
            $parts = explode('-', $lastInvoice->invoice_number);
            $sequence = (int) end($parts) + 1;
        } else {
            $sequence = 1;
        }

        return sprintf("INV-%s%s-%05d", $year, $month, $sequence);
    }

    /**
     * Generate PDF for invoice.
     * 
     * @param Invoice $invoice
     * @param Order $order
     * @return string PDF path
     */
    protected function generatePdf(Invoice $invoice, Order $order): string
    {
        $order->load(['items.product', 'user', 'shippingAddress']);

        $data = [
            'invoice' => $invoice,
            'order' => $order,
            'company' => [
                'name' => 'Shah Sports',
                'address' => config('app.company_address', 'Dhaka, Bangladesh'),
                'phone' => config('app.company_phone', ''),
                'email' => config('app.company_email', 'info@shahsports.com'),
            ],
        ];

        $pdf = Pdf::loadView('pdf.invoice', $data);
        
        $filename = "invoices/{$invoice->invoice_number}.pdf";
        Storage::put($filename, $pdf->output());

        return $filename;
    }

    /**
     * Get invoice PDF content.
     * 
     * @param Invoice $invoice
     * @return string|null
     */
    public function getInvoicePdf(Invoice $invoice): ?string
    {
        if (!$invoice->pdf_path || !Storage::exists($invoice->pdf_path)) {
            $this->regenerateInvoice($invoice);
        }

        return Storage::get($invoice->pdf_path);
    }

    /**
     * Download invoice PDF.
     * 
     * @param Invoice $invoice
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadInvoice(Invoice $invoice)
    {
        if (!$invoice->pdf_path || !Storage::exists($invoice->pdf_path)) {
            $this->regenerateInvoice($invoice);
        }

        return Storage::download($invoice->pdf_path, "invoice-{$invoice->invoice_number}.pdf");
    }
}
