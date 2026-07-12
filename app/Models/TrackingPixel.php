<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackingPixel extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider',
        'name',
        'pixel_id',
        'custom_head_script',
        'custom_body_script',
        'placement',
        'is_active',
        'gtm_dashboard_url',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Supported tracking providers.
     */
    public const PROVIDERS = [
        'facebook_pixel',
        'google_ads',
        'google_analytics',
        'gtm',
        'custom',
    ];

    /**
     * Scope to only active pixels (used by the public storefront endpoint).
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Validate the pixel_id format for the given provider.
     * Returns true when the id looks structurally valid, false otherwise.
     */
    public function isPixelIdValid(): bool
    {
        $id = trim((string) $this->pixel_id);

        switch ($this->provider) {
            case 'facebook_pixel':
                // Facebook pixel ids are numeric, typically 15-16 digits.
                return (bool) preg_match('/^\d{10,20}$/', $id);
            case 'google_ads':
                // Google Ads conversion ids look like AW-123456789.
                return (bool) preg_match('/^AW-\d{6,15}$/i', $id);
            case 'google_analytics':
                // GA4 measurement ids look like G-XXXXXXXXXX (older UA-XXXX-Y also allowed).
                return (bool) preg_match('/^(G-[A-Z0-9]{6,12}|UA-\d{4,12}-\d{1,4})$/i', $id);
            case 'gtm':
                // Google Tag Manager container ids look like GTM-XXXXXXX.
                return (bool) preg_match('/^GTM-[A-Z0-9]{5,10}$/i', $id);
            case 'custom':
                // Custom snippets don't require a pixel id.
                return true;
            default:
                return false;
        }
    }
}
