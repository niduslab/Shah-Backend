<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $message;
    public $chatId;

    public function __construct($message, $chatId)
    {
        $this->message = $message;
        $this->chatId = $chatId;
    }

    public function broadcastOn()
    {
        return new Channel('chat.' . $this->chatId); // Broadcasting to public chat channel
    }
}



