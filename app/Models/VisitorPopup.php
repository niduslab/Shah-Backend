<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitorPopup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'ip_address',
        'user_agent',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    /**
     * Scope for recent submissions.
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('submitted_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for submissions with email.
     */
    public function scopeWithEmail($query)
    {
        return $query->whereNotNull('email');
    }

    /**
     * Scope for submissions with phone.
     */
    public function scopeWithPhone($query)
    {
        return $query->whereNotNull('phone');
    }
}
