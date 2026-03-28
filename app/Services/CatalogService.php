<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Variation;
use App\Models\VariationOption;
use App\Services\Contracts\CatalogServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CatalogService implements CatalogServiceInterface
{
    /**
     * Create a new product.
     * 
     * @param array $data
     * @return Product
     */
    public function createProduct(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            // Generate slug if not provided
            if (empty($data['slug'])) {
                $data['slug'] = $this->generateUniqueSlug($data['name']);
            }

            // Generate SKU if not provided
            if (empty($data['sku'])) {
                $data['sku'] = $this->generateUniqueSku();
            }

            $product = Product::create([
                'category_id' => $data['category_id'],
                'brand_id' => $data['brand_id'] ?? null,
                'model_id' => $data['model_id'] ?? null,
                'shipping_class_id' => $data['shipping_class_id'] ?? null,
                'name' => $data['name'],
                'slug' => $data['slug'],
                'sku' => $data['sku'],
                'short_description' => $data['short_description'] ?? null,
                'description' => $data['description'] ?? null,
                'price' => $data['price'],
                'compare_price' => $data['compare_price'] ?? null,
                'cost_price' => $data['cost_price'] ?? null,
                'quantity' => $data['quantity'] ?? 0,
                'low_stock_threshold' => $data['low_stock_threshold'] ?? 5,
                'weight' => $data['weight'] ?? null,
                'weight_unit' => $data['weight_unit'] ?? 'kg',
                'length' => $data['length'] ?? null,
                'width' => $data['width'] ?? null,
                'height' => $data['height'] ?? null,
                'shipping_type' => $data['shipping_type'] ?? 'default',
                'shipping_cost' => $data['shipping_cost'] ?? null,
                'requires_shipping' => $data['requires_shipping'] ?? true,
                'separate_shipping' => $data['separate_shipping'] ?? false,
                'shipping_notes' => $data['shipping_notes'] ?? null,
                'is_featured' => $data['is_featured'] ?? false,
                'is_trending' => $data['is_trending'] ?? false,
                'status' => $data['status'] ?? 'draft',
                'meta_title' => $data['meta_title'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
                'meta_keywords' => $data['meta_keywords'] ?? null,
                'is_preorder' => $data['is_preorder'] ?? false,
                'preorder_release_date' => $data['preorder_release_date'] ?? null,
                'preorder_limit' => $data['preorder_limit'] ?? null,
                'preorder_deposit_amount' => $data['preorder_deposit_amount'] ?? null,
                'preorder_deposit_type' => $data['preorder_deposit_type'] ?? null,
            ]);

            // Handle images if provided
            if (!empty($data['images'])) {
                $this->syncProductImages($product, $data['images']);
            }

            // Handle variations if provided
            if (!empty($data['variations'])) {
                $this->syncProductVariations($product, $data['variations']);
            }

            return $product->load(['category', 'brand', 'model', 'images', 'variations.variationValues.variationOption.variation']);
        });
    }

    /**
     * Update an existing product.
     * 
     * @param Product $product
     * @param array $data
     * @return Product
     */
    public function updateProduct(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            // Handle slug update
            if (isset($data['name']) && $data['name'] !== $product->name && empty($data['slug'])) {
                $data['slug'] = $this->generateUniqueSlug($data['name'], $product->id);
            }

            $product->update(array_filter([
                'category_id' => $data['category_id'] ?? $product->category_id,
                'brand_id' => $data['brand_id'] ?? $product->brand_id,
                'model_id' => $data['model_id'] ?? $product->model_id,
                'shipping_class_id' => $data['shipping_class_id'] ?? $product->shipping_class_id,
                'name' => $data['name'] ?? $product->name,
                'slug' => $data['slug'] ?? $product->slug,
                'short_description' => $data['short_description'] ?? $product->short_description,
                'description' => $data['description'] ?? $product->description,
                'price' => $data['price'] ?? $product->price,
                'compare_price' => $data['compare_price'] ?? $product->compare_price,
                'cost_price' => $data['cost_price'] ?? $product->cost_price,
                'quantity' => $data['quantity'] ?? $product->quantity,
                'low_stock_threshold' => $data['low_stock_threshold'] ?? $product->low_stock_threshold,
                'weight' => $data['weight'] ?? $product->weight,
                'weight_unit' => $data['weight_unit'] ?? $product->weight_unit,
                'length' => $data['length'] ?? $product->length,
                'width' => $data['width'] ?? $product->width,
                'height' => $data['height'] ?? $product->height,
                'shipping_type' => $data['shipping_type'] ?? $product->shipping_type,
                'shipping_cost' => $data['shipping_cost'] ?? $product->shipping_cost,
                'requires_shipping' => $data['requires_shipping'] ?? $product->requires_shipping,
                'separate_shipping' => $data['separate_shipping'] ?? $product->separate_shipping,
                'shipping_notes' => $data['shipping_notes'] ?? $product->shipping_notes,
                'is_featured' => $data['is_featured'] ?? $product->is_featured,
                'is_trending' => $data['is_trending'] ?? $product->is_trending,
                'status' => $data['status'] ?? $product->status,
                'meta_title' => $data['meta_title'] ?? $product->meta_title,
                'meta_description' => $data['meta_description'] ?? $product->meta_description,
                'meta_keywords' => $data['meta_keywords'] ?? $product->meta_keywords,
                'is_preorder' => $data['is_preorder'] ?? $product->is_preorder,
                'preorder_release_date' => $data['preorder_release_date'] ?? $product->preorder_release_date,
                'preorder_limit' => $data['preorder_limit'] ?? $product->preorder_limit,
                'preorder_deposit_amount' => $data['preorder_deposit_amount'] ?? $product->preorder_deposit_amount,
                'preorder_deposit_type' => $data['preorder_deposit_type'] ?? $product->preorder_deposit_type,
            ], fn($value) => $value !== null));

            // Handle images if provided
            if (isset($data['images'])) {
                $this->syncProductImages($product, $data['images']);
            }

            // Handle variations if provided
            if (isset($data['variations'])) {
                $this->syncProductVariations($product, $data['variations']);
            }

            return $product->fresh(['category', 'brand', 'model', 'images', 'variations.variationValues.variationOption.variation']);
        });
    }

    /**
     * Get product with all variations loaded.
     * 
     * @param int $productId
     * @return Product|null
     */
    public function getProductWithVariations(int $productId): ?Product
    {
        return Product::with([
            'category',
            'brand',
            'model',
            'shippingClass',
            'images',
            'variations.variationValues.variationOption.variation',
            'reviews' => fn($q) => $q->approved()->latest()->limit(10),
        ])->find($productId);
    }

    /**
     * Get product by slug with all relations.
     * 
     * @param string $slug
     * @return Product|null
     */
    public function getProductBySlug(string $slug): ?Product
    {
        return Product::where('slug', $slug)
            ->active()
            ->with([
                'category',
                'brand',
                'model',
                'shippingClass',
                'images' => fn($q) => $q->orderBy('sort_order'),
                'variations.variationValues.variationOption.variation',
                'reviews' => fn($q) => $q->approved()->latest()->limit(10),
            ])
            ->first();
    }

    /**
     * Search products with filters.
     * 
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function searchProducts(array $filters): LengthAwarePaginator
    {
        $query = Product::query()->with(['category', 'brand', 'images']);

        // Only show active products for public queries
        if (!isset($filters['include_inactive']) || !$filters['include_inactive']) {
            $query->active();
        }

        // Search by keyword
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // Filter by brand
        if (!empty($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }

        // Filter by price range
        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }
        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        // Filter by stock status
        if (isset($filters['in_stock']) && $filters['in_stock']) {
            $query->where('quantity', '>', 0);
        }

        // Filter by featured
        if (isset($filters['is_featured']) && $filters['is_featured']) {
            $query->featured();
        }

        // Filter by trending
        if (isset($filters['is_trending']) && $filters['is_trending']) {
            $query->trending();
        }

        // Filter by preorder
        if (isset($filters['is_preorder'])) {
            $isPreorder = filter_var($filters['is_preorder'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($isPreorder === true) {
                $query->preorder();
            }
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';

        switch ($sortBy) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'name':
                $query->orderBy('name', $sortOrder);
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            default:
                $query->orderBy($sortBy, $sortOrder);
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->paginate($perPage);
    }

    /**
     * Get products by category.
     * 
     * @param int $categoryId
     * @param bool $includeSubcategories
     * @return LengthAwarePaginator
     */
    public function getProductsByCategory(int $categoryId, bool $includeSubcategories = true): LengthAwarePaginator
    {
        $categoryIds = [$categoryId];

        if ($includeSubcategories) {
            $categoryIds = array_merge($categoryIds, $this->getSubcategoryIds($categoryId));
        }

        return Product::active()
            ->whereIn('category_id', $categoryIds)
            ->with(['category', 'brand', 'images'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
    }

    /**
     * Get products by brand.
     * 
     * @param int $brandId
     * @return LengthAwarePaginator
     */
    public function getProductsByBrand(int $brandId): LengthAwarePaginator
    {
        return Product::active()
            ->where('brand_id', $brandId)
            ->with(['category', 'brand', 'images'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
    }

    /**
     * Get featured products.
     * 
     * @param int $limit
     * @return Collection
     */
    public function getFeaturedProducts(int $limit = 10): Collection
    {
        return Product::active()
            ->featured()
            ->with(['category', 'brand', 'images'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get trending products.
     * 
     * @param int $limit
     * @return Collection
     */
    public function getTrendingProducts(int $limit = 10): Collection
    {
        return Product::active()
            ->trending()
            ->with(['category', 'brand', 'images'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Delete a product.
     * 
     * @param Product $product
     * @return bool
     */
    public function deleteProduct(Product $product): bool
    {
        return $product->delete();
    }

    /**
     * Generate unique slug for product.
     * 
     * @param string $name
     * @param int|null $excludeId
     * @return string
     */
    protected function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (Product::where('slug', $slug)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Generate unique SKU.
     * 
     * @return string
     */
    protected function generateUniqueSku(): string
    {
        do {
            $sku = 'SS-' . strtoupper(Str::random(8));
        } while (Product::where('sku', $sku)->exists());

        return $sku;
    }

    /**
     * Get all subcategory IDs recursively.
     * 
     * @param int $categoryId
     * @return array
     */
    protected function getSubcategoryIds(int $categoryId): array
    {
        $ids = [];
        $children = Category::where('parent_id', $categoryId)->pluck('id')->toArray();

        foreach ($children as $childId) {
            $ids[] = $childId;
            $ids = array_merge($ids, $this->getSubcategoryIds($childId));
        }

        return $ids;
    }

    /**
     * Sync product images.
     * 
     * @param Product $product
     * @param array $images
     * @return void
     */
    protected function syncProductImages(Product $product, array $images): void
    {
        // Delete existing images
        $product->images()->delete();

        // Ensure only one primary image
        $hasPrimary = false;
        foreach ($images as $image) {
            if (isset($image['is_primary']) && $image['is_primary']) {
                $hasPrimary = true;
                break;
            }
        }

        // Create new images
        foreach ($images as $index => $image) {
            $isPrimary = isset($image['is_primary']) && $image['is_primary'];
            
            // If no primary is set, make the first image primary
            if (!$hasPrimary && $index === 0) {
                $isPrimary = true;
            }

            $product->images()->create([
                'image_path' => $image['path'],
                'alt_text' => $image['alt_text'] ?? null,
                'is_primary' => $isPrimary,
                'sort_order' => $image['sort_order'] ?? $index,
            ]);
        }
    }

    /**
     * Sync product variations.
     * 
     * @param Product $product
     * @param array $variations
     * @return void
     */
    protected function syncProductVariations(Product $product, array $variations): void
    {
        $existingVariationIds = [];

        foreach ($variations as $index => $variationData) {
            // Check if this is an update (has ID) or create (no ID)
            if (isset($variationData['id'])) {
                // Update existing variation
                $variation = $product->variations()->find($variationData['id']);
                
                if ($variation) {
                    // Check if marked for deletion
                    if (isset($variationData['_delete']) && $variationData['_delete']) {
                        $variation->delete();
                        continue;
                    }

                    // Update variation
                    $updateData = [];
                    if (isset($variationData['sku'])) {
                        $updateData['sku'] = $variationData['sku'];
                    }
                    if (isset($variationData['price'])) {
                        $updateData['price'] = $variationData['price'];
                    }
                    if (isset($variationData['quantity'])) {
                        $updateData['quantity'] = $variationData['quantity'];
                    }
                    if (isset($variationData['shipping_type'])) {
                        $updateData['shipping_type'] = $variationData['shipping_type'];
                    }
                    if (isset($variationData['shipping_cost'])) {
                        $updateData['shipping_cost'] = $variationData['shipping_cost'];
                    }
                    if (isset($variationData['is_default'])) {
                        $updateData['is_default'] = $variationData['is_default'];
                        
                        // If setting as default, unset others
                        if ($variationData['is_default']) {
                            $product->variations()->where('id', '!=', $variation->id)->update(['is_default' => false]);
                        }
                    }

                    if (!empty($updateData)) {
                        $variation->update($updateData);
                    }

                    // Update variation values if provided
                    $this->syncVariationValues($variation, $variationData);

                    $existingVariationIds[] = $variation->id;
                } else {
                    // Variation ID doesn't belong to this product, treat as new
                    $sku = $variationData['sku'] ?? $this->generateVariationSku($product, $index + 1);
                    $isDefault = $variationData['is_default'] ?? ($index === 0 && $product->variations()->count() === 0);

                    $variation = $product->variations()->create([
                        'sku' => $sku,
                        'price' => $variationData['price'] ?? null,
                        'quantity' => $variationData['quantity'] ?? 0,
                        'is_default' => $isDefault,
                        'shipping_type' => $variationData['shipping_type'] ?? 'inherit',
                        'shipping_cost' => $variationData['shipping_cost'] ?? null,
                    ]);

                    // If this is set as default, unset other defaults
                    if ($isDefault) {
                        $product->variations()->where('id', '!=', $variation->id)->update(['is_default' => false]);
                    }

                    // Create variation values
                    $this->syncVariationValues($variation, $variationData);

                    $existingVariationIds[] = $variation->id;
                }
            } else {
                // Create new variation
                $sku = $variationData['sku'] ?? $this->generateVariationSku($product, $index + 1);
                $isDefault = $variationData['is_default'] ?? ($index === 0 && $product->variations()->count() === 0);

                $variation = $product->variations()->create([
                    'sku' => $sku,
                    'price' => $variationData['price'] ?? null,
                    'quantity' => $variationData['quantity'] ?? 0,
                    'is_default' => $isDefault,
                    'shipping_type' => $variationData['shipping_type'] ?? 'inherit',
                    'shipping_cost' => $variationData['shipping_cost'] ?? null,
                ]);

                // If this is set as default, unset other defaults
                if ($isDefault) {
                    $product->variations()->where('id', '!=', $variation->id)->update(['is_default' => false]);
                }

                // Create variation values
                $this->syncVariationValues($variation, $variationData);

                $existingVariationIds[] = $variation->id;
            }
        }
    }

    /**
     * Sync variation values from either variation_values array or attributes object.
     */
    protected function syncVariationValues(ProductVariation $variation, array $variationData): void
    {
        // Delete existing variation values
        $variation->variationValues()->delete();

        // Handle variation_values format (array of option IDs)
        if (!empty($variationData['variation_values'])) {
            foreach ($variationData['variation_values'] as $optionId) {
                $variation->variationValues()->create([
                    'variation_option_id' => $optionId,
                ]);
            }
            return;
        }

        // Handle attributes format (key-value pairs like color: "Red", size: "XL")
        if (!empty($variationData['attributes'])) {
            \Log::info('Processing variation attributes', [
                'variation_id' => $variation->id,
                'attributes' => $variationData['attributes']
            ]);

            foreach ($variationData['attributes'] as $attributeName => $attributeValue) {
                // Skip empty values
                if (empty($attributeValue)) {
                    \Log::warning('Skipping empty attribute value', ['attribute' => $attributeName]);
                    continue;
                }

                \Log::info('Processing attribute', [
                    'name' => $attributeName,
                    'value' => $attributeValue
                ]);

                // Find or create the variation (attribute type like "Color", "Size")
                $variationModel = Variation::whereRaw('LOWER(name) = ?', [strtolower($attributeName)])->first();
                
                if (!$variationModel) {
                    // Create the variation if it doesn't exist
                    $variationModel = Variation::create([
                        'name' => ucfirst($attributeName),
                        'is_active' => true,
                    ]);
                    \Log::info('Created new variation', ['id' => $variationModel->id, 'name' => $variationModel->name]);
                }

                // Find or create the option by value
                $option = VariationOption::where('variation_id', $variationModel->id)
                    ->whereRaw('LOWER(value) = ?', [strtolower($attributeValue)])
                    ->first();

                if (!$option) {
                    // Create the option if it doesn't exist
                    $maxSortOrder = VariationOption::where('variation_id', $variationModel->id)->max('sort_order') ?? 0;
                    $option = VariationOption::create([
                        'variation_id' => $variationModel->id,
                        'value' => $attributeValue,
                        'label' => $attributeValue,
                        'is_active' => true,
                        'sort_order' => $maxSortOrder + 1,
                    ]);
                    \Log::info('Created new option', ['id' => $option->id, 'value' => $option->value]);
                }

                // Create the variation value link
                $variationValue = $variation->variationValues()->create([
                    'variation_option_id' => $option->id,
                ]);
                \Log::info('Created variation value link', [
                    'variation_value_id' => $variationValue->id,
                    'product_variation_id' => $variation->id,
                    'option_id' => $option->id
                ]);
            }
        } else {
            \Log::warning('No variation_values or attributes found', [
                'variation_id' => $variation->id,
                'data_keys' => array_keys($variationData)
            ]);
        }
    }

    /**
     * Generate unique SKU for variation.
     * 
     * @param Product $product
     * @param int $counter
     * @return string
     */
    protected function generateVariationSku(Product $product, int $counter = 1): string
    {
        $baseSku = $product->sku;

        do {
            $sku = $baseSku . '-V' . $counter;
            $counter++;
        } while (\App\Models\ProductVariation::where('sku', $sku)->exists());

        return $sku;
    }
}
