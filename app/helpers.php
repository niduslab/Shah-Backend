<?php

use App\Events\NotificationEvent;
use App\Models\Notification;

if (!function_exists('sendNotification')) {
    function sendNotification($userId, $shopId, $notificationType, $title, $message)
    {
        // Create a new notification
        $notification = Notification::create([
            'user_id' => $userId,
            'shop_id' => $shopId,
            'notification_type' => $notificationType,
            'title' => $title,
            'message' => $message,
        ]);

        // Broadcast the event
        event(new NotificationEvent($notification));

        return $notification;
    }
}


