<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\VisitorPopup;
use Illuminate\Http\Request;

class VisitorPopupController extends Controller
{
    /**
     * Display a listing of visitor popup submissions.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search');
        $hasEmail = $request->input('has_email');
        $hasPhone = $request->input('has_phone');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = VisitorPopup::query()->orderBy('submitted_at', 'desc');

        // Search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Email filter
        if ($hasEmail !== null) {
            if ($hasEmail === 'true' || $hasEmail === '1') {
                $query->withEmail();
            } else {
                $query->whereNull('email');
            }
        }

        // Phone filter
        if ($hasPhone !== null) {
            if ($hasPhone === 'true' || $hasPhone === '1') {
                $query->withPhone();
            } else {
                $query->whereNull('phone');
            }
        }

        // Date range filter
        if ($dateFrom) {
            $query->where('submitted_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->where('submitted_at', '<=', $dateTo);
        }

        $visitors = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $visitors,
        ]);
    }

    /**
     * Display the specified visitor popup submission.
     */
    public function show($id)
    {
        $visitor = VisitorPopup::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $visitor,
        ]);
    }

    /**
     * Remove the specified visitor popup submission.
     */
    public function destroy($id)
    {
        $visitor = VisitorPopup::findOrFail($id);
        $visitor->delete();

        return response()->json([
            'success' => true,
            'message' => 'Visitor submission deleted successfully',
        ]);
    }

    /**
     * Get statistics for visitor popup submissions.
     */
    public function statistics(Request $request)
    {
        $days = $request->input('days', 30);

        $total = VisitorPopup::count();
        $recent = VisitorPopup::recent($days)->count();
        $withEmail = VisitorPopup::withEmail()->count();
        $withPhone = VisitorPopup::withPhone()->count();
        $withBoth = VisitorPopup::withEmail()->withPhone()->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_submissions' => $total,
                'recent_submissions' => $recent,
                'with_email' => $withEmail,
                'with_phone' => $withPhone,
                'with_both' => $withBoth,
                'days' => $days,
            ],
        ]);
    }

    /**
     * Export visitor popup submissions.
     */
    public function export(Request $request)
    {
        $format = $request->input('format', 'csv');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = VisitorPopup::query()->orderBy('submitted_at', 'desc');

        if ($dateFrom) {
            $query->where('submitted_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->where('submitted_at', '<=', $dateTo);
        }

        $visitors = $query->get();

        if ($format === 'csv') {
            $filename = 'visitor_popups_' . now()->format('Y-m-d_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function () use ($visitors) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['ID', 'Name', 'Email', 'Phone', 'IP Address', 'Submitted At']);

                foreach ($visitors as $visitor) {
                    fputcsv($file, [
                        $visitor->id,
                        $visitor->name,
                        $visitor->email,
                        $visitor->phone,
                        $visitor->ip_address,
                        $visitor->submitted_at->format('Y-m-d H:i:s'),
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        return response()->json([
            'success' => true,
            'data' => $visitors,
        ]);
    }
}
