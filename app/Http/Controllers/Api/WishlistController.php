<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    /**
     * Get user's wishlist.
     */
    public function index(Request $request): JsonResponse
    {
        $wishlists = $request->user()
            ->wishlists()
            ->with(['product.images', 'product.category', 'product.brand'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $wishlists,
        ]);
    }

    /**
     * Add product to wishlist.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        // Check if product exists and is active
        $product = Product::where('id', $validated['product_id'])
            ->where('status', 'active')
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found or not available.',
            ], 404);
        }

        // Check if already in wishlist
        $exists = $request->user()
            ->wishlists()
            ->where('product_id', $validated['product_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Product already in wishlist.',
            ], 400);
        }

        $wishlist = $request->user()->wishlists()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Product added to wishlist.',
            'data' => $wishlist->load('product'),
        ], 201);
    }

    /**
     * Remove product from wishlist.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $wishlist = $request->user()->wishlists()->find($id);

        if (!$wishlist) {
            return response()->json([
                'success' => false,
                'message' => 'Wishlist item not found.',
            ], 404);
        }

        $wishlist->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product removed from wishlist.',
        ]);
    }

    /**
     * Remove product from wishlist by product ID.
     */
    public function removeByProduct(Request $request, int $productId): JsonResponse
    {
        $wishlist = $request->user()
            ->wishlists()
            ->where('product_id', $productId)
            ->first();

        if (!$wishlist) {
            return response()->json([
                'success' => false,
                'message' => 'Product not in wishlist.',
            ], 404);
        }

        $wishlist->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product removed from wishlist.',
        ]);
    }

    /**
     * Check if product is in wishlist.
     */
    public function check(Request $request, int $productId): JsonResponse
    {
        $inWishlist = $request->user()
            ->wishlists()
            ->where('product_id', $productId)
            ->exists();

        return response()->json([
            'success' => true,
            'data' => [
                'in_wishlist' => $inWishlist,
            ],
        ]);
    }

    /**
     * Clear entire wishlist.
     */
    public function clear(Request $request): JsonResponse
    {
        $request->user()->wishlists()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Wishlist cleared successfully.',
        ]);
    }
}
