<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Services\Contracts\InventoryServiceInterface;
use App\Services\Contracts\OrderServiceInterface;
use App\Services\Contracts\PaymentServiceInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class POSController extends Controller
{
    public function __construct(
        protected OrderServiceInterface $orderService,
        protected PaymentServiceInterface $paymentService,
        protected InventoryServiceInterface $inventoryService
    ) {}

    /**
     * Create a POS order.
     */
    public function createOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variation_id' => 'nullable|exists:product_variations,id',
            'items.*.quantity' => 'required|integer|min:1',
            'discount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|in:cash,card,manual',
            'notes' => 'nullable|string',
        ]);

        // Validate stock availability
        foreach ($validated['items'] as $item) {
            $product = Product::find($item['product_id']);
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => "Product not found.",
                ], 404);
            }

            $variation = isset($item['variation_id']) 
                ? ProductVariation::find($item['variation_id']) 
                : null;

            $available = $this->inventoryService->checkAvailability(
                $product,
                $item['quantity'],
                $variation
            );

            if (!$available) {
                return response()->json([
                    'success' => false,
                    'message' => "Insufficient stock for product: {$product->name}",
                ], 400);
            }
        }

        $customerData = [
            'name' => $validated['customer_name'],
            'email' => $validated['customer_email'],
            'phone' => $validated['customer_phone'] ?? null,
        ];

        $order = $this->orderService->createPosOrder(
            $customerData,
            $validated['items'],
            $validated['discount'] ?? null
        );

        // Record manual payment
        $this->paymentService->recordManualPayment(
            $order,
            $order->total_amount,
            $validated['payment_method'] ?? 'cash'
        );

        if (!empty($validated['notes'])) {
            $order->update(['notes' => $validated['notes']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'POS order created successfully.',
            'data' => $order->load('items.product'),
        ], 201);
    }

    /**
     * Search products for POS.
     */
    public function searchProducts(Request $request): JsonResponse
    {
        $search = $request->get('search', '');

        $products = Product::where('status', 'active')
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            })
            ->with(['variations', 'images' => fn($q) => $q->where('is_primary', true)])
            ->limit(20)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'price' => $product->price,
                    'quantity' => $product->quantity,
                    'image' => $product->images->first()?->path,
                    'variations' => $product->variations->map(fn($v) => [
                        'id' => $v->id,
                        'sku' => $v->sku,
                        'price' => $v->price ?? $product->price,
                        'quantity' => $v->quantity,
                    ]),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Get product by SKU or barcode.
     */
    public function getProductBySku(string $sku): JsonResponse
    {
        $product = Product::where('sku', $sku)
            ->where('status', 'active')
            ->with(['variations', 'images'])
            ->first();

        if (!$product) {
            // Check variations
            $variation = ProductVariation::where('sku', $sku)
                ->with(['product.images'])
                ->first();

            if ($variation) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'id' => $variation->product->id,
                        'name' => $variation->product->name,
                        'sku' => $variation->product->sku,
                        'price' => $variation->product->price,
                        'image' => $variation->product->images->first()?->path,
                        'selected_variation' => [
                            'id' => $variation->id,
                            'sku' => $variation->sku,
                            'price' => $variation->price ?? $variation->product->price,
                            'quantity' => $variation->quantity,
                        ],
                    ],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->price,
                'quantity' => $product->quantity,
                'image' => $product->images->first()?->path,
                'variations' => $product->variations->map(fn($v) => [
                    'id' => $v->id,
                    'sku' => $v->sku,
                    'price' => $v->price ?? $product->price,
                    'quantity' => $v->quantity,
                ]),
            ],
        ]);
    }

    /**
     * Calculate order totals preview.
     */
    public function calculateTotals(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variation_id' => 'nullable|exists:product_variations,id',
            'items.*.quantity' => 'required|integer|min:1',
            'discount' => 'nullable|numeric|min:0',
        ]);

        $subtotal = 0;
        $itemDetails = [];

        foreach ($validated['items'] as $item) {
            $product = Product::find($item['product_id']);
            $variation = isset($item['variation_id']) 
                ? ProductVariation::find($item['variation_id']) 
                : null;

            $price = $variation?->price ?? $product->price;
            $itemTotal = $price * $item['quantity'];
            $subtotal += $itemTotal;

            $itemDetails[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'variation_id' => $variation?->id,
                'quantity' => $item['quantity'],
                'unit_price' => $price,
                'total' => $itemTotal,
            ];
        }

        $discount = $validated['discount'] ?? 0;
        $total = max(0, $subtotal - $discount);

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $itemDetails,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
            ],
        ]);
    }

    /**
     * Generate a quotation PDF without creating an order.
     */
    public function generateQuotation(Request $request): Response
    {
        $validated = $request->validate([
            'customer_name'  => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'items'          => 'required|array|min:1',
            'items.*.product_id'  => 'required|exists:products,id',
            'items.*.variation_id' => 'nullable|exists:product_variations,id',
            'items.*.quantity'    => 'required|integer|min:1',
            'discount'       => 'nullable|numeric|min:0|max:100',
            'notes'          => 'nullable|string',
        ]);

        $subtotal = 0;
        $lineItems = [];

        foreach ($validated['items'] as $item) {
            $product  = Product::find($item['product_id']);
            $variation = isset($item['variation_id']) ? ProductVariation::find($item['variation_id']) : null;
            $price     = (float) ($variation?->price ?? $product->price);
            $lineTotal = $price * $item['quantity'];
            $subtotal += $lineTotal;

            $lineItems[] = [
                'name'       => $product->name,
                'sku'        => $variation?->sku ?? $product->sku,
                'quantity'   => $item['quantity'],
                'unit_price' => $price,
                'total'      => $lineTotal,
            ];
        }

        $discountPercent = (float) ($validated['discount'] ?? 0);
        $discountAmount  = ($subtotal * $discountPercent) / 100;
        $total           = max(0, $subtotal - $discountAmount);

        $quotationNumber = 'QT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
        $date            = now()->format('M d, Y');
        $validUntil      = now()->addDays(30)->format('M d, Y');

        $company = [
            'name'    => config('app.company_name', config('app.name')),
            'address' => config('app.company_address', ''),
            'phone'   => config('app.company_phone', ''),
            'email'   => config('app.company_email', config('mail.from.address', '')),
        ];

        $pdf = Pdf::loadView('pdf.quotation', [
            'quotation_number' => $quotationNumber,
            'date'             => $date,
            'valid_until'      => $validUntil,
            'company'          => $company,
            'customer'         => [
                'name'  => $validated['customer_name'],
                'email' => $validated['customer_email'] ?? '',
                'phone' => $validated['customer_phone'] ?? '',
            ],
            'items'            => $lineItems,
            'subtotal'         => $subtotal,
            'discount_percent' => $discountPercent,
            'discount_amount'  => $discountAmount,
            'total'            => $total,
            'notes'            => $validated['notes'] ?? '',
        ])->setPaper('a4');

        $filename = 'quotation-' . $quotationNumber . '.pdf';

        return $pdf->download($filename);
    }
}
