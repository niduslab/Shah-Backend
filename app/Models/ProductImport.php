<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'filename',
        'file_path',
        'status',
        'total_rows',
        'processed_rows',
        'successful_rows',
        'failed_rows',
        'errors',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'errors' => 'array',
        'total_rows' => 'integer',
        'processed_rows' => 'integer',
        'successful_rows' => 'integer',
        'failed_rows' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the user who initiated the import.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get progress percentage.
     */
    public function getProgressPercentageAttribute(): float
    {
        if ($this->total_rows === 0) {
            return 0;
        }

        return round(($this->processed_rows / $this->total_rows) * 100, 2);
    }

    /**
     * Check if import is in progress.
     */
    public function isInProgress(): bool
    {
        return in_array($this->status, ['pending', 'processing']);
    }

    /**
     * Check if import is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if import has failed.
     */
    public function hasFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Mark import as started.
     */
    public function markAsStarted(): void
    {
        $this->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);
    }

    /**
     * Mark import as completed.
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark import as failed.
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'completed_at' => now(),
        ]);
    }

    /**
     * Increment processed rows.
     */
    public function incrementProcessed(bool $success = true): void
    {
        $this->increment('processed_rows');
        
        if ($success) {
            $this->increment('successful_rows');
        } else {
            $this->increment('failed_rows');
        }
    }

    /**
     * Add error for a specific row.
     * Caps at 1000 errors to prevent unbounded JSON growth.
     */
    public function addRowError(int $rowNumber, array $errors): void
    {
        $currentErrors = $this->errors ?? [];
        
        // Cap at 1000 errors to prevent JSON column overflow
        if (count($currentErrors) >= 1000) {
            // Only update the cap message once
            if (!isset($currentErrors[999]['capped'])) {
                $currentErrors[999] = [
                    'row' => 'multiple',
                    'errors' => ['Error limit reached. Only first 1000 errors are shown. Check logs for complete details.'],
                    'capped' => true
                ];
                $this->update(['errors' => $currentErrors]);
            }
            return;
        }
        
        $currentErrors[] = [
            'row' => $rowNumber,
            'errors' => $errors,
        ];
        
        $this->update(['errors' => $currentErrors]);
    }

    /**
     * Scope for user's imports.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for in-progress imports.
     */
    public function scopeInProgress($query)
    {
        return $query->whereIn('status', ['pending', 'processing']);
    }

    /**
     * Scope for completed imports.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
