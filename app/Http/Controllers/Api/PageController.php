<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\CmsPage;
use App\Models\StorePolicy;
use Illuminate\Http\JsonResponse;

class PageController extends Controller
{
    /**
     * Get store policy by type.
     */
    public function policy(string $type): JsonResponse
    {
        $policy = StorePolicy::where('policy_type', $type)
            ->where('is_active', true)
            ->first();

        if (!$policy) {
            return response()->json([
                'success' => false,
                'message' => 'Policy not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $policy,
        ]);
    }

    /**
     * Get CMS page by slug.
     */
    public function page(string $slug): JsonResponse
    {
        $page = CmsPage::where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $page,
        ]);
    }

    /**
     * Get active banners.
     */
    public function banners(string $position = null): JsonResponse
    {
        $query = Banner::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')
                  ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')
                  ->orWhere('ends_at', '>=', now());
            });

        if ($position) {
            $query->where('position', $position);
        }

        $banners = $query->orderBy('sort_order')->get();

        return response()->json([
            'success' => true,
            'data' => $banners,
        ]);
    }

    /**
     * Get all policies.
     */
    public function allPolicies(): JsonResponse
    {
        $policies = StorePolicy::where('is_active', true)
            ->select(['title', 'slug', 'policy_type'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $policies,
        ]);
    }
}
