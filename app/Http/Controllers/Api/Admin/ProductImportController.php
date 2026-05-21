<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessProductImport;
use App\Models\ProductImport;
use App\Services\ProductImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductImportController extends Controller
{
    public function __construct(
        protected ProductImportService $importService
    ) {}

    /**
     * Upload CSV file and start import process.
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240', // Max 10MB
        ]);

        try {
            $file = $request->file('file');
            $userId = Auth::id();

            // Validate and store file
            $import = $this->importService->validateAndStoreFile($file, $userId);

            // Dispatch background job
            ProcessProductImport::dispatch($import);

            return response()->json([
                'success' => true,
                'message' => 'Import started successfully. Processing in background.',
                'data' => [
                    'import_id' => $import->id,
                    'filename' => $import->filename,
                    'total_rows' => $import->total_rows,
                    'status' => $import->status,
                ],
            ], 201);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to start import: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get import status and progress.
     */
    public function status(int $id): JsonResponse
    {
        $import = ProductImport::find($id);

        if (!$import) {
            return response()->json([
                'success' => false,
                'message' => 'Import not found.',
            ], 404);
        }

        // Check if user has access
        if ($import->user_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $import->id,
                'filename' => $import->filename,
                'status' => $import->status,
                'total_rows' => $import->total_rows,
                'processed_rows' => $import->processed_rows,
                'successful_rows' => $import->successful_rows,
                'failed_rows' => $import->failed_rows,
                'progress_percentage' => $import->progress_percentage,
                'error_message' => $import->error_message,
                'started_at' => $import->started_at?->toISOString(),
                'completed_at' => $import->completed_at?->toISOString(),
                'created_at' => $import->created_at->toISOString(),
            ],
        ]);
    }

    /**
     * Get import errors.
     */
    public function errors(int $id): JsonResponse
    {
        $import = ProductImport::find($id);

        if (!$import) {
            return response()->json([
                'success' => false,
                'message' => 'Import not found.',
            ], 404);
        }

        // Check if user has access
        if ($import->user_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'import_id' => $import->id,
                'filename' => $import->filename,
                'failed_rows' => $import->failed_rows,
                'errors' => $import->errors ?? [],
            ],
        ]);
    }

    /**
     * List all imports for current user.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ProductImport::query()
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        $perPage = $request->input('per_page', 15);
        $imports = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $imports,
        ]);
    }

    /**
     * Download CSV template.
     */
    public function template(): \Illuminate\Http\Response
    {
        $csv = $this->importService->generateCsvTemplate();

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="product_import_template.csv"',
        ]);
    }

    /**
     * Cancel an import.
     */
    public function cancel(int $id): JsonResponse
    {
        $import = ProductImport::find($id);

        if (!$import) {
            return response()->json([
                'success' => false,
                'message' => 'Import not found.',
            ], 404);
        }

        // Check if user has access
        if ($import->user_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.',
            ], 403);
        }

        // Can only cancel pending or processing imports
        if (!$import->isInProgress()) {
            return response()->json([
                'success' => false,
                'message' => 'Can only cancel imports that are pending or in progress.',
            ], 422);
        }

        $import->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Import cancelled successfully.',
        ]);
    }

    /**
     * Delete an import record.
     */
    public function destroy(int $id): JsonResponse
    {
        $import = ProductImport::find($id);

        if (!$import) {
            return response()->json([
                'success' => false,
                'message' => 'Import not found.',
            ], 404);
        }

        // Check if user has access
        if ($import->user_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.',
            ], 403);
        }

        // Can't delete in-progress imports
        if ($import->isInProgress()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete an import that is in progress. Cancel it first.',
            ], 422);
        }

        // Delete file
        $this->importService->deleteImportFile($import);

        // Delete record
        $import->delete();

        return response()->json([
            'success' => true,
            'message' => 'Import deleted successfully.',
        ]);
    }

    /**
     * Export failed rows to CSV.
     */
    public function exportErrors(int $id): \Illuminate\Http\Response|JsonResponse
    {
        $import = ProductImport::find($id);

        if (!$import) {
            return response()->json([
                'success' => false,
                'message' => 'Import not found.',
            ], 404);
        }

        // Check if user has access
        if ($import->user_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.',
            ], 403);
        }

        if (empty($import->errors)) {
            return response()->json([
                'success' => false,
                'message' => 'No errors to export.',
            ], 404);
        }

        // Create CSV with proper escaping using fputcsv
        $output = fopen('php://temp', 'r+');
        
        // Write header
        fputcsv($output, ['Row Number', 'Errors']);
        
        // Write error rows with proper escaping
        foreach ($import->errors as $error) {
            $rowNumber = $error['row'];
            $errorMessages = implode('; ', $error['errors']);
            fputcsv($output, [$rowNumber, $errorMessages]);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        $filename = 'import_errors_' . $import->id . '_' . date('Y-m-d_His') . '.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }
}
