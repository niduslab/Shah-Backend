<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',
        'amount',
        'currency',
        'transaction_id',
        'reference_number',
        'proof_path',
        'note',
        'recorded_by',
        'payment_method',
        'status',
        'gateway_response',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response' => 'array',
        'paid_at' => 'datetime',
    ];

    protected $appends = ['proof_url'];

    /**
     * Get the full URL of the proof document, if any.
     */
    public function getProofUrlAttribute(): ?string
    {
        if (!$this->proof_path) {
            return null;
        }

        return Storage::disk('public')->url($this->proof_path);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
