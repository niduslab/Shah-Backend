<?php

namespace App\Services;

use App\Models\InventoryLog;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Services\Contracts\VariationServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VariationService implements VariationServiceInterface
{
    /**
     * Create a new variation for a product.
     * 
     * @param Product $product
     * @param array $options Contains sku, price, quantity, is_default, variation_values
     * @return ProductVariation
     */
    public function createVariation(Product $product, array $options): ProductVariation
    {
        return DB::transaction(function () use ($product, $options) {
            // Generate SKU if not provided
            $sku = $options['sku'] ?? $this->generateVariationSku($product);

            $variation = ProductVariation::create([
                'product_id' => $product->id,
                'sku' => $sku,
                'price' => $options['price'] ?? null,
                'quantity' => $options['quantity'] ?? 0,
                'is_default' => $options['is_default'] ?? false,
            ]);

            // If this is set as default, unset other defaults
            if ($variation->is_default) {
                ProductVariation::where('product_id', $product->id)
                    ->where('id', '!=', $variation->id)
                    ->update(['is_default' => false]);
            }

            // Create variation values if provided
            if (!empty($options['variation_values'])) {
                foreach ($options['variation_values'] as $variationOptionId) {
                    $variation->variationValues()->create([
                        'variation_option_id' => $variationOptionId,
                    ]);
                }
            }

            // Log initial inventory
            if ($variation->quantity > 0) {
                $this->logInventoryChange(
                    $product,
                    $variation,
                    0,
                    $variation->quantity,
                    'restock',
                    'Initial stock for new variation'
                );
            }

            return $variation->load('variationValues');
        });
    }

    /**
     * Update variation stock.
     * 
     * @param ProductVariation $variation
     * @param int $quantity New quantity or adjustment
     * @param string $reason Reason for stock change
     * @return void
     */
    public function updateStock(ProductVariation $variation, int $quantity, string $reason): void
    {
        DB::transaction(function () use ($variation, $quantity, $reason) {
            $quantityBefore = $variation->quantity;
            $quantityChange = $quantity - $quantityBefore;

            $variation->update(['quantity' => $quantity]);

            $this->logInventoryChange(
                $variation->product,
                $variation,
                $quantityBefore,
                $quantityChange,
                $reason
            );
        });
    }

    /**
     * Adjust variation stock by a delta amount.
     * 
     * @param ProductVariation $variation
     * @param int $adjustment Positive or negative adjustment
     * @param string $reason
     * @param string|null $referenceType
     * @param int|null $referenceId
     * @return void
     */
    public function adjustStock(
        ProductVariation $variation,
        int $adjustment,
        string $reason,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): void {
        DB::transaction(function () use ($variation, $adjustment, $reason, $referenceType, $referenceId) {
            $quantityBefore = $variation->quantity;
            $newQuantity = max(0, $quantityBefore + $adjustment);

            $variation->update(['quantity' => $newQuantity]);

            $this->logInventoryChange(
                $variation->product,
                $variation,
                $quantityBefore,
                $adjustment,
                $reason,
                null,
                $referenceType,
                $referenceId
            );
        });
    }

    /**
     * Get available variation options for a product.
     * 
     * @param Product $product
     * @return array
     */
    public function getAvailableOptions(Product $product): array
    {
        $variations = $product->variations()
            ->with('variationValues')
            ->where('quantity', '>', 0)
            ->get();

        $options = [];

        foreach ($variations as $variation) {
            foreach ($variation->variationValues as $value) {
                $optionId = $value->variation_option_id;
                if (!isset($options[$optionId])) {
                    $options[$optionId] = [
                        'option_id' => $optionId,
                        'variations' => [],
                    ];
                }
                $options[$optionId]['variations'][] = [
                    'variation_id' => $variation->id,
                    'sku' => $variation->sku,
                    'price' => $variation->price ?? $product->price,
                    'quantity' => $variation->quantity,
                ];
            }
        }

        return array_values($options);
    }

    /**
     * Update variation details.
     * 
     * @param ProductVariation $variation
     * @param array $data
     * @return ProductVariation
     */
    public function updateVariation(ProductVariation $variation, array $data): ProductVariation
    {
        return DB::transaction(function () use ($variation, $data) {
            $updateData = [];

            if (isset($data['sku'])) {
                $updateData['sku'] = $data['sku'];
            }

            if (isset($data['price'])) {
                $updateData['price'] = $data['price'];
            }

            if (isset($data['is_default'])) {
                $updateData['is_default'] = $data['is_default'];

                // If setting as default, unset other defaults
                if ($data['is_default']) {
                    ProductVariation::where('product_id', $variation->product_id)
                        ->where('id', '!=', $variation->id)
                        ->update(['is_default' => false]);
                }
            }

            // Handle quantity separately with logging
            if (isset($data['quantity']) && $data['quantity'] !== $variation->quantity) {
                $this->updateStock($variation, $data['quantity'], $data['reason'] ?? 'adjustment');
            }

            if (!empty($updateData)) {
                $variation->update($updateData);
            }

            // Update variation values if provided
            if (isset($data['variation_values'])) {
                $variation->variationValues()->delete();
                foreach ($data['variation_values'] as $variationOptionId) {
                    $variation->variationValues()->create([
                        'variation_option_id' => $variationOptionId,
                    ]);
                }
            }

            return $variation->fresh('variationValues');
        });
    }

    /**
     * Delete a variation.
     * 
     * @param ProductVariation $variation
     * @return bool
     */
    public function deleteVariation(ProductVariation $variation): bool
    {
        return $variation->delete();
    }

    /**
     * Find variation by attributes.
     * 
     * @param Product $product
     * @param array $attributes Array of variation_option_ids
     * @return ProductVariation|null
     */
    public function findVariationByAttributes(Product $product, array $attributes): ?ProductVariation
    {
        $variations = $product->variations()->with('variationValues')->get();

        foreach ($variations as $variation) {
            $variationOptionIds = $variation->variationValues->pluck('variation_option_id')->toArray();
            sort($variationOptionIds);
            sort($attributes);

            if ($variationOptionIds === $attributes) {
                return $variation;
            }
        }

        return null;
    }

    /**
     * Generate unique SKU for variation.
     * 
     * @param Product $product
     * @return string
     */
    protected function generateVariationSku(Product $product): string
    {
        $baseSku = $product->sku;
        $counter = $product->variations()->count() + 1;

        do {
            $sku = $baseSku . '-V' . $counter;
            $counter++;
        } while (ProductVariation::where('sku', $sku)->exists());

        return $sku;
    }

    /**
     * Log inventory change.
     * 
     * @param Product $product
     * @param ProductVariation $variation
     * @param int $quantityBefore
     * @param int $quantityChange
     * @param string $reason
     * @param string|null $notes
     * @param string|null $referenceType
     * @param int|null $referenceId
     * @return void
     */
    protected function logInventoryChange(
        Product $product,
        ProductVariation $variation,
        int $quantityBefore,
        int $quantityChange,
        string $reason,
        ?string $notes = null,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): void {
        InventoryLog::create([
            'product_id' => $product->id,
            'product_variation_id' => $variation->id,
            'quantity_before' => $quantityBefore,
            'quantity_change' => $quantityChange,
            'quantity_after' => $quantityBefore + $quantityChange,
            'reason' => $reason,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'notes' => $notes,
            'created_by' => Auth::id(),
        ]);
    }
}
