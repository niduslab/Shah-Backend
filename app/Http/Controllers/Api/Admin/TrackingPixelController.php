<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrackingPixel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Centralized management of third-party tracking / pixel integrations
 * (Facebook Pixel, Google Ads, Google Analytics, Google Tag Manager, custom snippets).
 */
class TrackingPixelController extends Controller
{
    /**
     * List all configured tracking pixels.
     */
    public function index(Request $request)
    {
        $query = TrackingPixel::query()->orderBy('created_at', 'desc');

        if ($provider = $request->input('provider')) {
            $query->where('provider', $provider);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $pixels = $query->get()->map(function ($pixel) {
            $pixel->id_valid = $pixel->isPixelIdValid();
            return $pixel;
        });

        return response()->json([
            'success' => true,
            'data' => $pixels,
        ]);
    }

    /**
     * Show a single tracking pixel.
     */
    public function show($id)
    {
        $pixel = TrackingPixel::findOrFail($id);
        $pixel->id_valid = $pixel->isPixelIdValid();

        return response()->json([
            'success' => true,
            'data' => $pixel,
        ]);
    }

    /**
     * Create a new tracking pixel.
     */
    public function store(Request $request)
    {
        $data = $this->validatePixel($request);

        $pixel = TrackingPixel::create($data);
        $pixel->id_valid = $pixel->isPixelIdValid();

        return response()->json([
            'success' => true,
            'message' => 'Tracking pixel created successfully.',
            'data' => $pixel,
        ], 201);
    }

    /**
     * Update an existing tracking pixel.
     */
    public function update(Request $request, $id)
    {
        $pixel = TrackingPixel::findOrFail($id);

        $data = $this->validatePixel($request, $id);

        $pixel->update($data);
        $pixel->id_valid = $pixel->isPixelIdValid();

        return response()->json([
            'success' => true,
            'message' => 'Tracking pixel updated successfully.',
            'data' => $pixel,
        ]);
    }

    /**
     * Delete a tracking pixel.
     */
    public function destroy($id)
    {
        $pixel = TrackingPixel::findOrFail($id);
        $pixel->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tracking pixel deleted successfully.',
        ]);
    }

    /**
     * Toggle the active state of a tracking pixel.
     */
    public function toggle($id)
    {
        $pixel = TrackingPixel::findOrFail($id);
        $pixel->update(['is_active' => ! $pixel->is_active]);

        return response()->json([
            'success' => true,
            'message' => $pixel->is_active
                ? 'Tracking pixel activated.'
                : 'Tracking pixel deactivated.',
            'data' => $pixel,
        ]);
    }

    /**
     * Verify that the configured pixel id / snippet looks structurally valid.
     * This is a format check — it confirms the integration is well-formed before
     * it goes live on the storefront.
     */
    public function verify($id)
    {
        $pixel = TrackingPixel::findOrFail($id);

        $valid = $pixel->isPixelIdValid();
        $hasSnippet = $pixel->provider === 'custom'
            ? (bool) ($pixel->custom_head_script || $pixel->custom_body_script)
            : true;

        $verified = $valid && $hasSnippet;

        return response()->json([
            'success' => true,
            'data' => [
                'verified' => $verified,
                'id_valid' => $valid,
                'has_snippet' => $hasSnippet,
                'message' => $verified
                    ? 'Configuration looks valid and is ready to go live.'
                    : 'Configuration is incomplete or the ID format is invalid for this provider.',
            ],
        ]);
    }

    /**
     * Shared validation rules for store/update.
     */
    protected function validatePixel(Request $request, $id = null): array
    {
        return $request->validate([
            'provider' => ['required', Rule::in(TrackingPixel::PROVIDERS)],
            'name' => ['required', 'string', 'max:255'],
            'pixel_id' => ['nullable', 'string', 'max:255'],
            'custom_head_script' => ['nullable', 'string'],
            'custom_body_script' => ['nullable', 'string'],
            'placement' => ['nullable', Rule::in(['head', 'body_top', 'body_bottom'])],
            'is_active' => ['sometimes', 'boolean'],
            'gtm_dashboard_url' => ['nullable', 'url', 'max:2048'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
