<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignRecipient extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'campaign_id',
        'user_id',
        'email',
        'status',
        'sent_at',
        'opened_at',
        'clicked_at',
        'created_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Get the campaign.
     */
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Get the user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark as sent.
     */
    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    /**
     * Mark as opened.
     */
    public function markAsOpened(): void
    {
        if ($this->status === 'sent') {
            $this->update([
                'status' => 'opened',
                'opened_at' => now(),
            ]);
        }
    }

    /**
     * Mark as clicked.
     */
    public function markAsClicked(): void
    {
        $this->update([
            'status' => 'clicked',
            'clicked_at' => now(),
        ]);
    }

    /**
     * Scope for pending recipients.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for sent recipients.
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }
}
