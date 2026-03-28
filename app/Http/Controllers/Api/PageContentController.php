<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PageContent;

class PageContentController extends Controller
{
    /**
     * Get page content by page key (for frontend)
     */
    public function getByPageKey($pageKey)
    {
        $pageContents = PageContent::with('brand')
            ->byPageKey($pageKey)
            ->active()
            ->ordered()
            ->get();

        if ($pageContents->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No content found for this page'
            ], 404);
        }

        // Get meta information from first section if available
        $firstSection = $pageContents->first();
        $meta = [
            'title' => $firstSection->meta_title,
            'description' => $firstSection->meta_description,
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'page_key' => $pageKey,
                'page_type' => $firstSection->page_type,
                'meta' => $meta,
                'sections' => $pageContents
            ]
        ]);
    }

    /**
     * Get brand page content by brand slug
     */
    public function getByBrandSlug($brandSlug)
    {
        $pageContents = PageContent::with('brand')
            ->whereHas('brand', function ($query) use ($brandSlug) {
                $query->where('slug', $brandSlug);
            })
            ->active()
            ->ordered()
            ->get();

        if ($pageContents->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No content found for this brand'
            ], 404);
        }

        $firstSection = $pageContents->first();
        $meta = [
            'title' => $firstSection->meta_title,
            'description' => $firstSection->meta_description,
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'brand' => $firstSection->brand,
                'page_type' => $firstSection->page_type,
                'meta' => $meta,
                'sections' => $pageContents
            ]
        ]);
    }
}
