<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    /**
     * Display a listing of contact message submissions.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search');
        $status = $request->input('status');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = ContactMessage::query()->orderBy('created_at', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($dateFrom) {
            $query->where('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->where('created_at', '<=', $dateTo);
        }

        $messages = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $messages,
        ]);
    }

    /**
     * Display the specified contact message and mark it as read.
     */
    public function show($id)
    {
        $message = ContactMessage::findOrFail($id);

        if ($message->status === 'new') {
            $message->update(['status' => 'read']);
        }

        return response()->json([
            'success' => true,
            'data' => $message,
        ]);
    }

    /**
     * Update the status of a contact message.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:new,read,replied',
        ]);

        $message = ContactMessage::findOrFail($id);
        $message->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'data' => $message,
        ]);
    }

    /**
     * Remove the specified contact message.
     */
    public function destroy($id)
    {
        $message = ContactMessage::findOrFail($id);
        $message->delete();

        return response()->json([
            'success' => true,
            'message' => 'Contact message deleted successfully',
        ]);
    }

    /**
     * Get statistics for contact message submissions.
     */
    public function statistics(Request $request)
    {
        $days = $request->input('days', 30);

        $total = ContactMessage::count();
        $recent = ContactMessage::recent($days)->count();
        $new = ContactMessage::new()->count();
        $replied = ContactMessage::where('status', 'replied')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_messages' => $total,
                'recent_messages' => $recent,
                'new_messages' => $new,
                'replied_messages' => $replied,
                'days' => $days,
            ],
        ]);
    }
}
