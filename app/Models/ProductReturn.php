<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductReturn extends Model
{
    use HasFactory;
    protected $table = 'returns';

    protected $fillable = [
        'order_item_id',
        'user_id',
        'return_status',
        'reason',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_item_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

