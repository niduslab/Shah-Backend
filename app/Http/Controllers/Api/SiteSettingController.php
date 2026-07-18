<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Storage;

/**
 * Public endpoint exposing contact info, social links, and the payment
 * banner so the storefront footer/header can render them dynamically.
 */
class SiteSettingController extends Controller
{
    public function show()
    {
        $settings = SiteSetting::current();

        return response()->json([
            'success' => true,
            'data' => [
                'contact_email' => $settings->contact_email,
                'contact_phone' => $settings->contact_phone,
                'contact_address' => $settings->contact_address,
                'facebook_url' => $settings->facebook_url,
                'twitter_url' => $settings->twitter_url,
                'instagram_url' => $settings->instagram_url,
                'youtube_url' => $settings->youtube_url,
                'linkedin_url' => $settings->linkedin_url,
                'payment_banner_url' => $settings->payment_banner_path
                    ? Storage::disk('public')->url($settings->payment_banner_path)
                    : null,
            ],
        ]);
    }
}
