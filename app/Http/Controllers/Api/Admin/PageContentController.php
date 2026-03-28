<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PageContentController extends Controller
{
    /**
     * Display a listing of page contents
     */
    public function index(Request $request)
    {
        $query = PageContent::with(['brand', 'creator', 'updater']);

        // Filter by page_key
        if ($request->has('page_key')) {
            $query->byPageKey($request->page_key);
        }

        // Filter by page_type
        if ($request->has('page_type')) {
            $query->byPageType($request->page_type);
        }

        // Filter by brand_id
        if ($request->has('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Order by sort_order
        $query->ordered();

        $pageContents = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $pageContents
        ]);
    }

    /**
     * Store a newly created page content
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page_key' => 'required|string|max:100',
            'page_type' => 'required|in:landing,brand',
            'section_name' => 'required|string|max:100',
            'title' => 'required|string',
            'sort_order' => 'nullable|integer',
            'brand_id' => 'nullable|exists:brands,id',
            'content' => 'required|array',
            'meta_title' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        $pageContent = PageContent::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Page content created successfully',
            'data' => $pageContent->load(['brand', 'creator', 'updater'])
        ], 201);
    }

    /**
     * Display the specified page content
     */
    public function show($id)
    {
        $pageContent = PageContent::with(['brand', 'creator', 'updater'])->find($id);

        if (!$pageContent) {
            return response()->json([
                'success' => false,
                'message' => 'Page content not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $pageContent
        ]);
    }

    /**
     * Update the specified page content
     */
    public function update(Request $request, $id)
    {
        $pageContent = PageContent::find($id);

        if (!$pageContent) {
            return response()->json([
                'success' => false,
                'message' => 'Page content not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'page_key' => 'sometimes|string|max:100',
            'page_type' => 'sometimes|in:landing,brand',
            'section_name' => 'sometimes|string|max:100',
            'title' => 'sometimes|string',
            'sort_order' => 'nullable|integer',
            'brand_id' => 'nullable|exists:brands,id',
            'content' => 'sometimes|array',
            'meta_title' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        $data['updated_by'] = auth()->id();

        $pageContent->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Page content updated successfully',
            'data' => $pageContent->load(['brand', 'creator', 'updater'])
        ]);
    }

    /**
     * Remove the specified page content
     */
    public function destroy($id)
    {
        $pageContent = PageContent::find($id);

        if (!$pageContent) {
            return response()->json([
                'success' => false,
                'message' => 'Page content not found'
            ], 404);
        }

        $pageContent->delete();

        return response()->json([
            'success' => true,
            'message' => 'Page content deleted successfully'
        ]);
    }

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

        return response()->json([
            'success' => true,
            'data' => $pageContents
        ]);
    }

    /**
     * Bulk update sort order
     */
    public function updateSortOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.id' => 'required|exists:page_contents,id',
            'items.*.sort_order' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        foreach ($request->items as $item) {
            PageContent::where('id', $item['id'])->update([
                'sort_order' => $item['sort_order'],
                'updated_by' => auth()->id()
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sort order updated successfully'
        ]);
    }
}
