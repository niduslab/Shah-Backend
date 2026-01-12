<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'subtotal',
        'shipping_cost',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'notes',
        'pdf_path',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Boot method to auto-generate invoice number.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = self::generateInvoiceNumber();
            }
            if (empty($invoice->invoice_date)) {
                $invoice->invoice_date = now();
            }
        });
    }

    /**
     * Generate unique invoice number.
     */
    public static function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $year = now()->format('Y');
        $lastInvoice = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastInvoice ? (int) substr($lastInvoice->invoice_number, -6) + 1 : 1;

        return $prefix . $year . str_pad($sequence, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get the order.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Check if PDF exists.
     */
    public function hasPdf(): bool
    {
        return !empty($this->pdf_path) && file_exists(storage_path('app/' . $this->pdf_path));
    }
}
