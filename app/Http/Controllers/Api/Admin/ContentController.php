<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\CmsPage;
use App\Models\StorePolicy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ContentController extends Controller
{
    // Store Policies

    /**
     * List all store policies.
     */
    public function policies(): JsonResponse
    {
        $policies = StorePolicy::orderBy('policy_type')->get();

        return response()->json([
            'success' => true,
            'data' => $policies,
        ]);
    }

    /**
     * Get a specific policy.
     */
    public function showPolicy(int $id): JsonResponse
    {
        $policy = StorePolicy::find($id);

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
     * Store a new policy.
     */
    public function storePolicy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'policy_type' => 'required|in:privacy,terms,return,shipping,warranty',
            'content' => 'required|string',
            'is_active' => 'nullable|boolean',
        ]);

        $policy = StorePolicy::create([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']),
            'policy_type' => $validated['policy_type'],
            'content' => $validated['content'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Policy created successfully.',
            'data' => $policy,
        ], 201);
    }

    /**
     * Update a policy.
     */
    public function updatePolicy(Request $request, int $id): JsonResponse
    {
        $policy = StorePolicy::find($id);

        if (!$policy) {
            return response()->json([
                'success' => false,
                'message' => 'Policy not found.',
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'is_active' => 'nullable|boolean',
        ]);

        if (isset($validated['title'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $policy->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Policy updated successfully.',
            'data' => $policy,
        ]);
    }

    // CMS Pages

    /**
     * List all CMS pages.
     */
    public function pages(Request $request): JsonResponse
    {
        $query = CmsPage::query();

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $pages = $query->orderBy('title')->get();

        return response()->json([
            'success' => true,
            'data' => $pages,
        ]);
    }

    /**
     * Get a specific CMS page.
     */
    public function showPage(int $id): JsonResponse
    {
        $page = CmsPage::find($id);

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
     * Store a new CMS page.
     */
    public function storePage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $page = CmsPage::create([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']),
            'content' => $validated['content'],
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Page created successfully.',
            'data' => $page,
        ], 201);
    }

    /**
     * Update a CMS page.
     */
    public function updatePage(Request $request, int $id): JsonResponse
    {
        $page = CmsPage::find($id);

        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found.',
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        if (isset($validated['title'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $page->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Page updated successfully.',
            'data' => $page,
        ]);
    }

    /**
     * Delete a CMS page.
     */
    public function destroyPage(int $id): JsonResponse
    {
        $page = CmsPage::find($id);

        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found.',
            ], 404);
        }

        $page->delete();

        return response()->json([
            'success' => true,
            'message' => 'Page deleted successfully.',
        ]);
    }

    // Banners

    /**
     * List all banners.
     */
    public function banners(Request $request): JsonResponse
    {
        $query = Banner::query();

        if ($request->filled('position')) {
            $query->where('position', $request->position);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $banners = $query->orderBy('sort_order')->get();

        return response()->json([
            'success' => true,
            'data' => $banners,
        ]);
    }

    /**
     * Store a new banner.
     */
    public function storeBanner(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'image' => 'required|string',
            'link' => 'nullable|string|max:500',
            'position' => 'required|in:hero,sidebar,footer,popup',
            'sort_order' => 'nullable|integer|min:0',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after:starts_at',
            'is_active' => 'nullable|boolean',
        ]);

        $banner = Banner::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Banner created successfully.',
            'data' => $banner,
        ], 201);
    }

    /**
     * Update a banner.
     */
    public function updateBanner(Request $request, int $id): JsonResponse
    {
        $banner = Banner::find($id);

        if (!$banner) {
            return response()->json([
                'success' => false,
                'message' => 'Banner not found.',
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'image' => 'sometimes|string',
            'link' => 'nullable|string|max:500',
            'position' => 'sometimes|in:hero,sidebar,footer,popup',
            'sort_order' => 'nullable|integer|min:0',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date',
            'is_active' => 'nullable|boolean',
        ]);

        $banner->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Banner updated successfully.',
            'data' => $banner,
        ]);
    }

    /**
     * Delete a banner.
     */
    public function destroyBanner(int $id): JsonResponse
    {
        $banner = Banner::find($id);

        if (!$banner) {
            return response()->json([
                'success' => false,
                'message' => 'Banner not found.',
            ], 404);
        }

        $banner->delete();

        return response()->json([
            'success' => true,
            'message' => 'Banner deleted successfully.',
        ]);
    }
}
