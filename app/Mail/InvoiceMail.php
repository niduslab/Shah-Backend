<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
        public $order = null
    ) {
        // Load order if not provided
        if (!$this->order) {
            $this->order = $this->invoice->order;
        }
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invoice ' . $this->invoice->invoice_number . ' - Shah Sports',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoices.invoice',
            with: [
                'invoice' => $this->invoice->load('order.items'),
            ],
        );
    }

    public function attachments(): array
    {
        if ($this->invoice->pdf_path && file_exists(storage_path('app/' . $this->invoice->pdf_path))) {
            return [
                Attachment::fromStorage($this->invoice->pdf_path)
                    ->as('invoice-' . $this->invoice->invoice_number . '.pdf')
                    ->withMime('application/pdf'),
            ];
        }

        return [];
    }
}
