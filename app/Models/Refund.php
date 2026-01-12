<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id',
        'refund_amount',
        'refund_status',
        'refund_method',
        'refund_date',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_item_id');
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    // report and statistics
    public function scopeCompletedRefunds($query)
    {
        return $query->where('refund_status', 'completed');
    }
}
