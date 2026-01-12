<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Chat;

use Illuminate\Broadcasting\PrivateChannel;

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id; // Allow access only to the authenticated user
});



Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    // This can be empty or you can check some condition for the public chat
    return true; // Anyone can join this chat channel without authentication
});

