<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryLog;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Services\Contracts\InventoryServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function __construct(
        protected InventoryServiceInterface $inventoryService
    ) {}

    /**
     * Get inventory overview.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['variations.variationValues.variationOption.variation', 'images', 'category', 'brand'])
            ->select('products.*');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'low') {
                $query->whereRaw('quantity <= low_stock_threshold');
            } elseif ($request->stock_status === 'out') {
                $query->where('quantity', 0);
            } elseif ($request->stock_status === 'in') {
                $query->where('quantity', '>', 0);
            }
        }

        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 15);
        $products = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Get low stock products.
     */
    public function lowStock(): JsonResponse
    {
        $products = $this->inventoryService->getLowStockProducts();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Adjust inventory for a product.
     */
    public function adjust(Request $request, int $productId): JsonResponse
    {
        $product = Product::find($productId);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
            ], 404);
        }

        $validated = $request->validate([
            'variation_id' => 'nullable|exists:product_variations,id',
            'quantity' => 'required|integer',
            'reason' => 'required|in:adjustment,damage,return,recount,other',
            'notes' => 'nullable|string|max:500',
        ]);

        $variation = null;
        if (!empty($validated['variation_id'])) {
            $variation = ProductVariation::find($validated['variation_id']);
        }

        $this->inventoryService->adjustStock(
            $product,
            $validated['quantity'],
            $validated['reason'],
            $variation,
            $validated['notes'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Inventory adjusted successfully.',
            'data' => $product->fresh(['variations']),
        ]);
    }

    /**
     * Get inventory logs.
     */
    public function logs(Request $request): JsonResponse
    {
        $query = InventoryLog::with(['product', 'productVariation', 'createdBy']);

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('reason')) {
            $query->where('reason', $request->reason);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $perPage = $request->get('per_page', 15);
        $logs = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }

    /**
     * Get inventory for a specific product.
     */
    public function show(int $productId): JsonResponse
    {
        $product = Product::with(['variations.variationValues.variationOption.variation', 'category', 'brand'])->find($productId);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
            ], 404);
        }

        $logs = InventoryLog::where('product_id', $productId)
            ->with('createdBy')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'product' => $product,
                'recent_logs' => $logs,
            ],
        ]);
    }

    /**
     * Bulk adjust inventory.
     */
    public function bulkAdjust(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'adjustments' => 'required|array|min:1',
            'adjustments.*.product_id' => 'required|exists:products,id',
            'adjustments.*.variation_id' => 'nullable|exists:product_variations,id',
            'adjustments.*.quantity' => 'required|integer',
            'reason' => 'required|in:adjustment,damage,return,recount,other',
            'notes' => 'nullable|string|max:500',
        ]);

        foreach ($validated['adjustments'] as $adjustment) {
            $product = Product::find($adjustment['product_id']);
            
            $variation = null;
            if (!empty($adjustment['variation_id'])) {
                $variation = ProductVariation::find($adjustment['variation_id']);
            }

            $this->inventoryService->adjustStock(
                $product,
                $adjustment['quantity'],
                $validated['reason'],
                $variation,
                $validated['notes'] ?? null
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Bulk inventory adjustment completed.',
        ]);
    }
}
