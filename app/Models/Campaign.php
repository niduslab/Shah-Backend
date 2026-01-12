<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'subject',
        'content',
        'campaign_type',
        'target_type',
        'target_criteria',
        'scheduled_at',
        'sent_at',
        'status',
        'total_recipients',
        'total_sent',
        'total_opened',
        'total_clicked',
    ];

    protected $casts = [
        'target_criteria' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'total_recipients' => 'integer',
        'total_sent' => 'integer',
        'total_opened' => 'integer',
        'total_clicked' => 'integer',
    ];

    /**
     * Get campaign recipients.
     */
    public function recipients()
    {
        return $this->hasMany(CampaignRecipient::class);
    }

    /**
     * Check if campaign is draft.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if campaign is scheduled.
     */
    public function isScheduled(): bool
    {
        return $this->status === 'scheduled';
    }

    /**
     * Check if campaign is sent.
     */
    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    /**
     * Check if campaign can be edited.
     */
    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft', 'scheduled']);
    }

    /**
     * Get open rate percentage.
     */
    public function getOpenRateAttribute(): float
    {
        if ($this->total_sent === 0) {
            return 0;
        }

        return round(($this->total_opened / $this->total_sent) * 100, 2);
    }

    /**
     * Get click rate percentage.
     */
    public function getClickRateAttribute(): float
    {
        if ($this->total_sent === 0) {
            return 0;
        }

        return round(($this->total_clicked / $this->total_sent) * 100, 2);
    }

    /**
     * Scope for draft campaigns.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope for scheduled campaigns.
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    /**
     * Scope for sent campaigns.
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope for campaigns ready to send.
     */
    public function scopeReadyToSend($query)
    {
        return $query->where('status', 'scheduled')
            ->where('scheduled_at', '<=', now());
    }
}
