<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get admin notifications.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 20);

        $query = $request->user()->notifications();

        if ($request->has('read')) {
            $isRead = filter_var($request->get('read'), FILTER_VALIDATE_BOOLEAN);
            $isRead ? $query->whereNotNull('read_at') : $query->whereNull('read_at');
        }

        $notifications = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $notifications,
        ]);
    }

    /**
     * Get unread notifications count.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $count = $request->user()->unreadNotifications()->count();

        return response()->json([
            'success' => true,
            'data' => [
                'unread_count' => $count,
            ],
        ]);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found.',
            ], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read.',
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read.',
        ]);
    }

    /**
     * Delete a notification.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found.',
            ], 404);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted.',
        ]);
    }

    /**
     * Clear all notifications.
     */
    public function clear(Request $request): JsonResponse
    {
        $request->user()->notifications()->delete();

        return response()->json([
            'success' => true,
            'message' => 'All notifications cleared.',
        ]);
    }
}
