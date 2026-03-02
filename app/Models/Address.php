<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'address_line_1',
        'address_line_2',
        'contact_no',
        'city',
        'state',
        'zip_code',
        'address_type',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'shipping_address_id');
    }

    /**
     * Get full address as string.
     */
    public function getFullAddressAttribute(): string
    {
        $parts = [
            $this->address_line_1,
            $this->address_line_2,
            $this->city,
            $this->state,
            $this->zip_code,
        ];

        return implode(', ', array_filter($parts));
    }

    /**
     * Scope for default addresses.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope for shipping addresses.
     */
    public function scopeShipping($query)
    {
        return $query->where('address_type', 'shipping_address');
    }

    /**
     * Scope for billing addresses.
     */
    public function scopeBilling($query)
    {
        return $query->where('address_type', 'billing_address');
    }
}
