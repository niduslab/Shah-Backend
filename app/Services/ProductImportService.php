<?php

namespace App\Services;

use App\Models\ProductImport;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use League\Csv\Reader;
use League\Csv\Statement;

class ProductImportService
{
    /**
     * Validate and store uploaded CSV file.
     */
    public function validateAndStoreFile(UploadedFile $file, int $userId): ProductImport
    {
        // Validate file
        $validator = Validator::make(
            ['file' => $file],
            [
                'file' => 'required|file|mimes:csv,txt|max:10240', // Max 10MB
            ]
        );

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }

        // Store file
        $filename = 'product_imports/' . time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('product_imports', basename($filename), 'local');

        // Count rows (excluding header)
        $totalRows = $this->countCsvRows(storage_path('app/' . $path));

        // Create import record
        return ProductImport::create([
            'user_id' => $userId,
            'filename' => $file->getClientOriginalName(),
            'file_path' => $path,
            'status' => 'pending',
            'total_rows' => $totalRows,
        ]);
    }

    /**
     * Count rows in CSV file.
     */
    protected function countCsvRows(string $filePath): int
    {
        try {
            $csv = Reader::createFromPath($filePath, 'r');
            $csv->setHeaderOffset(0);
            
            return iterator_count($csv->getRecords());
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Read CSV file in chunks.
     */
    public function readCsvInChunks(string $filePath, int $offset = 0, int $limit = 100): array
    {
        $csv = Reader::createFromPath($filePath, 'r');
        $csv->setHeaderOffset(0);

        $statement = Statement::create()
            ->offset($offset)
            ->limit($limit);

        $records = $statement->process($csv);
        
        $rows = [];
        foreach ($records as $record) {
            $rows[] = $record;
        }

        return $rows;
    }

    /**
     * Validate a single product row.
     */
    public function validateProductRow(array $row, int $rowNumber): array
    {
        $errors = [];

        // Required fields
        if (empty($row['name'])) {
            $errors[] = 'Product name is required';
        }

        if (empty($row['category_id'])) {
            $errors[] = 'Category ID is required';
        } elseif (!is_numeric($row['category_id'])) {
            $errors[] = 'Category ID must be a number';
        } elseif (!\App\Models\Category::where('id', $row['category_id'])->exists()) {
            $errors[] = 'Category ID does not exist';
        }

        if (empty($row['price'])) {
            $errors[] = 'Price is required';
        } elseif (!is_numeric($row['price']) || $row['price'] < 0) {
            $errors[] = 'Price must be a positive number';
        }

        // SKU duplicate check (if provided)
        if (!empty($row['sku'])) {
            if (\App\Models\Product::where('sku', $row['sku'])->exists()) {
                $errors[] = 'Product SKU already exists in database';
            }
        }

        // NOTE: We don't validate variation SKU duplicates here
        // If duplicate, system will auto-generate unique SKU during import

        // Optional numeric fields
        if (!empty($row['compare_price']) && (!is_numeric($row['compare_price']) || $row['compare_price'] < 0)) {
            $errors[] = 'Compare price must be a positive number';
        }

        if (!empty($row['cost_price']) && (!is_numeric($row['cost_price']) || $row['cost_price'] < 0)) {
            $errors[] = 'Cost price must be a positive number';
        }

        if (!empty($row['quantity']) && (!is_numeric($row['quantity']) || $row['quantity'] < 0)) {
            $errors[] = 'Quantity must be a positive number';
        }

        if (!empty($row['weight']) && (!is_numeric($row['weight']) || $row['weight'] < 0)) {
            $errors[] = 'Weight must be a positive number';
        }

        // Foreign key validation
        if (!empty($row['brand_id'])) {
            if (!is_numeric($row['brand_id'])) {
                $errors[] = 'Brand ID must be a number';
            } elseif (!\App\Models\Brand::where('id', $row['brand_id'])->exists()) {
                $errors[] = 'Brand ID does not exist';
            }
        }

        if (!empty($row['model_id'])) {
            if (!is_numeric($row['model_id'])) {
                $errors[] = 'Model ID must be a number';
            } elseif (!\App\Models\ProductModel::where('id', $row['model_id'])->exists()) {
                $errors[] = 'Model ID does not exist';
            }
        }

        // Status validation
        if (!empty($row['status']) && !in_array($row['status'], ['active', 'inactive', 'draft'])) {
            $errors[] = 'Status must be one of: active, inactive, draft';
        }

        // Weight unit validation
        if (!empty($row['weight_unit']) && !in_array($row['weight_unit'], ['g', 'kg', 'lb'])) {
            $errors[] = 'Weight unit must be one of: g, kg, lb';
        }

        return $errors;
    }

    /**
     * Transform CSV row to product data array.
     */
    public function transformRowToProductData(array $row): array
    {
        $data = [
            'name' => $row['name'] ?? '',
            'category_id' => !empty($row['category_id']) ? (int)$row['category_id'] : null,
            'price' => !empty($row['price']) ? (float)$row['price'] : 0,
        ];

        // Optional fields
        $optionalFields = [
            'sku', 'brand_id', 'model_id', 'shipping_class_id',
            'short_description', 'description',
            'compare_price', 'cost_price', 'quantity', 'low_stock_threshold',
            'weight', 'weight_unit', 'length', 'width', 'height',
            'shipping_type', 'shipping_cost',
            'status', 'meta_title', 'meta_description', 'meta_keywords',
        ];

        foreach ($optionalFields as $field) {
            if (!empty($row[$field])) {
                $data[$field] = $row[$field];
            }
        }

        // Boolean fields
        $booleanFields = ['is_featured', 'is_trending', 'kinomap', 'requires_shipping', 'separate_shipping', 'is_preorder'];
        foreach ($booleanFields as $field) {
            if (isset($row[$field])) {
                $data[$field] = $this->toBool($row[$field]);
            }
        }

        // Numeric fields
        $numericFields = ['brand_id', 'model_id', 'shipping_class_id', 'quantity', 'low_stock_threshold'];
        foreach ($numericFields as $field) {
            if (!empty($row[$field]) && is_numeric($row[$field])) {
                $data[$field] = (int)$row[$field];
            }
        }

        $decimalFields = ['compare_price', 'cost_price', 'weight', 'length', 'width', 'height', 'shipping_cost'];
        foreach ($decimalFields as $field) {
            if (!empty($row[$field]) && is_numeric($row[$field])) {
                $data[$field] = (float)$row[$field];
            }
        }

        // Handle images (image_1 to image_10)
        $images = [];
        for ($i = 1; $i <= 10; $i++) {
            $imageKey = "image_$i";
            if (!empty($row[$imageKey])) {
                $images[] = [
                    'path' => $row[$imageKey],
                    'alt_text' => $row["image_{$i}_alt"] ?? null,
                    'is_primary' => $i === 1, // First image is primary
                    'sort_order' => $i - 1,
                ];
            }
        }
        if (!empty($images)) {
            $data['images'] = $images;
        }

        // Handle variations (variation_1 to variation_10)
        $variations = [];
        for ($i = 1; $i <= 10; $i++) {
            $skuKey = "variation_{$i}_sku";
            $attrKey = "variation_{$i}_attributes";
            $priceKey = "variation_{$i}_price";
            $qtyKey = "variation_{$i}_quantity";

            if (!empty($row[$attrKey])) {
                $variation = [
                    'sku' => $row[$skuKey] ?? null,
                    'price' => !empty($row[$priceKey]) ? (float)$row[$priceKey] : null,
                    'quantity' => !empty($row[$qtyKey]) ? (int)$row[$qtyKey] : 0,
                    'is_default' => $i === 1, // First variation is default
                    'sort_order' => $i - 1,
                ];

                // Parse attributes with improved delimiter handling
                // Format: "color:Red|size:XL" or use ~~ as delimiter for values with pipes
                // Example: "color:Red~~size:Large|XL" (if value contains pipe)
                $attributes = [];
                
                // Check if using alternative delimiter
                $delimiter = '|';
                if (strpos($row[$attrKey], '~~') !== false) {
                    $delimiter = '~~';
                }
                
                $attrPairs = explode($delimiter, $row[$attrKey]);
                foreach ($attrPairs as $pair) {
                    $parts = explode(':', $pair, 2);
                    if (count($parts) === 2) {
                        $attributes[trim($parts[0])] = trim($parts[1]);
                    }
                }
                if (!empty($attributes)) {
                    $variation['attributes'] = $attributes;
                }

                $variations[] = $variation;
            }
        }
        if (!empty($variations)) {
            $data['variations'] = $variations;
        }

        return $data;
    }

    /**
     * Convert various boolean representations to actual boolean.
     */
    protected function toBool($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        
        if (is_numeric($value)) {
            return (int)$value === 1;
        }
        
        if (is_string($value)) {
            $value = strtolower(trim($value));
            return in_array($value, ['true', '1', 'yes', 'on'], true);
        }
        
        return false;
    }

    /**
     * Generate CSV template with 3 images and 3 variations.
     */
    public function generateCsvTemplate(): string
    {
        $headers = [
            'name', 'sku', 'category_id', 'brand_id', 'model_id', 'shipping_class_id',
            'short_description', 'description',
            'price', 'compare_price', 'cost_price',
            'quantity', 'low_stock_threshold',
            'weight', 'weight_unit', 'length', 'width', 'height',
            'shipping_type', 'shipping_cost', 'requires_shipping', 'separate_shipping', 'shipping_notes',
            'is_featured', 'is_trending', 'kinomap',
            'status',
            'meta_title', 'meta_description', 'meta_keywords',
            'is_preorder', 'preorder_release_date', 'preorder_limit', 'preorder_deposit_amount', 'preorder_deposit_type',
        ];

        // Add image columns (3 images)
        for ($i = 1; $i <= 3; $i++) {
            $headers[] = "image_$i";
            $headers[] = "image_{$i}_alt";
        }

        // Add variation columns (3 variations)
        for ($i = 1; $i <= 3; $i++) {
            $headers[] = "variation_{$i}_sku";
            $headers[] = "variation_{$i}_attributes";
            $headers[] = "variation_{$i}_price";
            $headers[] = "variation_{$i}_quantity";
        }

        // Sample data row
        $sampleData = [
            'Treadmill Pro 3000', 'TM-3000', '5', '3', '', '',
            'High-performance treadmill', 'Professional grade treadmill with advanced features',
            '1299.99', '1599.99', '899.99',
            '25', '5',
            '85.5', 'kg', '180', '80', '150',
            'default', '', '1', '0', '',
            '1', '0', '0',
            'active',
            'Treadmill Pro 3000 - Professional Grade', 'Buy the best treadmill for home gym', 'treadmill,fitness,gym',
            '0', '', '', '', '',
        ];

        // Add sample images (3 images)
        for ($i = 1; $i <= 3; $i++) {
            $sampleData[] = "https://example.com/images/product-$i.jpg";
            $sampleData[] = "Product image $i";
        }

        // Add sample variations (3 variations)
        $variations = [
            ['TM-3000-V1', 'color:Black|warranty:2 Years', '1299.99', '15'],
            ['TM-3000-V2', 'color:Silver|warranty:2 Years', '1349.99', '10'],
            ['TM-3000-V3', 'color:White|warranty:3 Years', '1399.99', '8'],
        ];
        
        foreach ($variations as $variation) {
            $sampleData = array_merge($sampleData, $variation);
        }

        // Create CSV content
        $csv = implode(',', $headers) . "\n";
        $csv .= implode(',', array_map(function($value) {
            return '"' . str_replace('"', '""', $value) . '"';
        }, $sampleData));

        return $csv;
    }

    /**
     * Delete import file.
     */
    public function deleteImportFile(ProductImport $import): void
    {
        if (Storage::disk('local')->exists($import->file_path)) {
            Storage::disk('local')->delete($import->file_path);
        }
    }
}
