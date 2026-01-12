<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_variation_id',
        'product_name',
        'variation_details',
        'quantity',
        'unit_price',
        'total_price',
    ];

    protected $casts = [
        'variation_details' => 'array',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    /**
     * Get the order.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

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
     * Get returns for this item.
     */
    public function returns()
    {
        return $this->hasMany(ProductReturn::class);
    }

    /**
     * Calculate total price.
     */
    public function calculateTotal(): void
    {
        $this->total_price = $this->unit_price * $this->quantity;
    }
}
