<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_email',
        'contact_phone',
        'contact_address',
        'facebook_url',
        'twitter_url',
        'instagram_url',
        'youtube_url',
        'linkedin_url',
        'payment_banner_path',
    ];

    /**
     * Site settings are a singleton row. Fetch it, creating an empty row on first use.
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate([]);
    }
}
