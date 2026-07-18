<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Manages the singleton site settings row: contact info, social links,
 * and the payment/trust banner shown on the storefront.
 */
class SiteSettingController extends Controller
{
    /**
     * Show the current site settings.
     */
    public function show(): JsonResponse
    {
        $settings = SiteSetting::current();

        return response()->json([
            'success' => true,
            'data' => $this->present($settings),
        ]);
    }

    /**
     * Update the site settings. Accepts multipart/form-data when a new
     * payment banner file is included.
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:255'],
            'contact_address' => ['nullable', 'string', 'max:500'],
            'facebook_url' => ['nullable', 'url', 'max:2048'],
            'twitter_url' => ['nullable', 'url', 'max:2048'],
            'instagram_url' => ['nullable', 'url', 'max:2048'],
            'youtube_url' => ['nullable', 'url', 'max:2048'],
            'linkedin_url' => ['nullable', 'url', 'max:2048'],
            'payment_banner' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
            'remove_payment_banner' => ['nullable', 'boolean'],
        ]);

        $settings = SiteSetting::current();
        $uploadedPath = null;

        DB::beginTransaction();

        try {
            if ($request->hasFile('payment_banner')) {
                $uploadedPath = $request->file('payment_banner')->store('storage/site-settings', 'public');
                $validated['payment_banner_path'] = $uploadedPath;

                if ($settings->payment_banner_path && Storage::disk('public')->exists($settings->payment_banner_path)) {
                    Storage::disk('public')->delete($settings->payment_banner_path);
                }
            } elseif ($request->boolean('remove_payment_banner')) {
                if ($settings->payment_banner_path && Storage::disk('public')->exists($settings->payment_banner_path)) {
                    Storage::disk('public')->delete($settings->payment_banner_path);
                }
                $validated['payment_banner_path'] = null;
            }

            unset($validated['remove_payment_banner']);

            $settings->update($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Site settings updated successfully.',
                'data' => $this->present($settings),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            if ($uploadedPath && Storage::disk('public')->exists($uploadedPath)) {
                Storage::disk('public')->delete($uploadedPath);
            }

            throw $e;
        }
    }

    /**
     * Shape the settings row for API responses, resolving the banner to a full URL.
     */
    protected function present(SiteSetting $settings): array
    {
        $data = $settings->toArray();
        $data['payment_banner_url'] = $settings->payment_banner_path
            ? Storage::disk('public')->url($settings->payment_banner_path)
            : null;

        return $data;
    }
}
