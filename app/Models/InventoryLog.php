<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'product_variation_id',
        'quantity_before',
        'quantity_change',
        'quantity_after',
        'reason',
        'reference_type',
        'reference_id',
        'notes',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'quantity_before' => 'integer',
        'quantity_change' => 'integer',
        'quantity_after' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * Get the product.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the product variation.
     */
    public function productVariation()
    {
        return $this->belongsTo(ProductVariation::class);
    }

    /**
     * Get the user who created this log.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the reference model (polymorphic).
     */
    public function reference()
    {
        if (!$this->reference_type || !$this->reference_id) {
            return null;
        }

        $modelClass = 'App\\Models\\' . $this->reference_type;
        if (class_exists($modelClass)) {
            return $modelClass::find($this->reference_id);
        }

        return null;
    }

    /**
     * Check if this is a stock decrease.
     */
    public function isDecrease(): bool
    {
        return $this->quantity_change < 0;
    }

    /**
     * Check if this is a stock increase.
     */
    public function isIncrease(): bool
    {
        return $this->quantity_change > 0;
    }

    /**
     * Scope for specific product.
     */
    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope for specific reason.
     */
    public function scopeByReason($query, string $reason)
    {
        return $query->where('reason', $reason);
    }
}
