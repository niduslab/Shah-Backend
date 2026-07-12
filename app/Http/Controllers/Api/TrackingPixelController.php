<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TrackingPixel;

/**
 * Public endpoint that exposes the active tracking pixels so the storefront
 * can inject the corresponding scripts into the page.
 */
class TrackingPixelController extends Controller
{
    /**
     * Return all active tracking pixels for the storefront.
     * Only the fields required to render the scripts are exposed.
     */
    public function active()
    {
        $pixels = TrackingPixel::active()
            ->get()
            ->map(function ($pixel) {
                return [
                    'id' => $pixel->id,
                    'provider' => $pixel->provider,
                    'pixel_id' => $pixel->pixel_id,
                    'custom_head_script' => $pixel->custom_head_script,
                    'custom_body_script' => $pixel->custom_body_script,
                    'placement' => $pixel->placement,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $pixels,
        ]);
    }
}
