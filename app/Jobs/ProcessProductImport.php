<?php

namespace App\Jobs;

use App\Models\ProductImport;
use App\Services\Contracts\CatalogServiceInterface;
use App\Services\ProductImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessProductImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public $timeout = 3600; // 1 hour

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ProductImport $import,
        public int $chunkSize = 100
    ) {}

    /**
     * Execute the job.
     */
    public function handle(
        ProductImportService $importService,
        CatalogServiceInterface $catalogService
    ): void {
        try {
            Log::info("Starting product import", ['import_id' => $this->import->id]);

            // Mark as started
            $this->import->markAsStarted();

            $filePath = storage_path('app/' . $this->import->file_path);

            if (!file_exists($filePath)) {
                throw new \Exception("Import file not found: {$filePath}");
            }

            $offset = 0;
            $processedCount = 0;

            // Process in chunks
            while (true) {
                // Check for cancellation before processing each chunk
                $this->import->refresh();
                if ($this->import->status === 'cancelled') {
                    Log::info("Import cancelled by user", ['import_id' => $this->import->id]);
                    return;
                }

                $rows = $importService->readCsvInChunks($filePath, $offset, $this->chunkSize);

                if (empty($rows)) {
                    break; // No more rows to process
                }

                foreach ($rows as $index => $row) {
                    // Check for cancellation within the loop (every 10 rows for performance)
                    if ($index % 10 === 0) {
                        $this->import->refresh();
                        if ($this->import->status === 'cancelled') {
                            Log::info("Import cancelled by user during processing", ['import_id' => $this->import->id]);
                            return;
                        }
                    }

                    $rowNumber = $offset + $index + 2; // +2 for header row and 1-based indexing

                    try {
                        // Validate row
                        $errors = $importService->validateProductRow($row, $rowNumber);

                        if (!empty($errors)) {
                            $this->import->addRowError($rowNumber, $errors);
                            $this->import->incrementProcessed(false);
                            Log::warning("Row validation failed", [
                                'import_id' => $this->import->id,
                                'row' => $rowNumber,
                                'errors' => $errors
                            ]);
                            continue;
                        }

                        // Transform row to product data
                        $productData = $importService->transformRowToProductData($row);

                        // Create product using existing service
                        DB::beginTransaction();
                        try {
                            $product = $catalogService->createProduct($productData);
                            
                            // Increment BEFORE commit (inside transaction)
                            $this->import->incrementProcessed(true);
                            $processedCount++;
                            
                            DB::commit();

                            Log::info("Product created successfully", [
                                'import_id' => $this->import->id,
                                'row' => $rowNumber,
                                'product_id' => $product->id,
                                'sku' => $product->sku
                            ]);
                        } catch (\Exception $e) {
                            DB::rollBack();
                            
                            // Parse error message for better user feedback
                            $errorMessage = $e->getMessage();
                            
                            // Check for duplicate SKU errors
                            if (strpos($errorMessage, 'Duplicate entry') !== false && strpos($errorMessage, 'sku') !== false) {
                                if (strpos($errorMessage, 'product_variations') !== false) {
                                    // Extract SKU from error message
                                    preg_match("/Duplicate entry '([^']+)'/", $errorMessage, $matches);
                                    $duplicateSku = $matches[1] ?? 'unknown';
                                    $errorMessage = "Variation SKU '{$duplicateSku}' already exists in database. Please use a unique SKU or leave empty to auto-generate.";
                                } else {
                                    preg_match("/Duplicate entry '([^']+)'/", $errorMessage, $matches);
                                    $duplicateSku = $matches[1] ?? 'unknown';
                                    $errorMessage = "Product SKU '{$duplicateSku}' already exists in database. Please use a unique SKU or leave empty to auto-generate.";
                                }
                            }
                            
                            $this->import->addRowError($rowNumber, [
                                $errorMessage
                            ]);
                            $this->import->incrementProcessed(false);

                            Log::error("Product creation failed", [
                                'import_id' => $this->import->id,
                                'row' => $rowNumber,
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString()
                            ]);
                        }
                    } catch (\Exception $e) {
                        $this->import->addRowError($rowNumber, [
                            'Unexpected error: ' . $e->getMessage()
                        ]);
                        $this->import->incrementProcessed(false);

                        Log::error("Row processing failed", [
                            'import_id' => $this->import->id,
                            'row' => $rowNumber,
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                $offset += $this->chunkSize;

                // Refresh import model to get latest data
                $this->import->refresh();

                Log::info("Processed chunk", [
                    'import_id' => $this->import->id,
                    'offset' => $offset,
                    'processed' => $this->import->processed_rows,
                    'total' => $this->import->total_rows
                ]);
            }

            // Mark as completed
            $this->import->markAsCompleted();

            Log::info("Product import completed", [
                'import_id' => $this->import->id,
                'total_rows' => $this->import->total_rows,
                'successful' => $this->import->successful_rows,
                'failed' => $this->import->failed_rows
            ]);

        } catch (\Exception $e) {
            $this->import->markAsFailed($e->getMessage());

            Log::error("Product import failed", [
                'import_id' => $this->import->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $this->import->markAsFailed($exception->getMessage());

        Log::error("Product import job failed permanently", [
            'import_id' => $this->import->id,
            'error' => $exception->getMessage()
        ]);
    }
}
