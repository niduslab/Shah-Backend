<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Gallery::with('images')->active();

        if ($request->has('type')) {
            $query->byType($request->type);
        }

        $galleries = $query->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $galleries,
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $gallery = Gallery::with('images')
            ->active()
            ->where('slug', $slug)
            ->first();

        if (!$gallery) {
            return response()->json([
                'success' => false,
                'message' => 'Gallery not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $gallery,
        ]);
    }
}
