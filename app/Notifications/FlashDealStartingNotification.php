<?php

namespace App\Notifications;

use App\Models\FlashDeal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class FlashDealStartingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $flashDeal;

    public function __construct(FlashDeal $flashDeal)
    {
        $this->flashDeal = $flashDeal;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'flash_deal_starting',
            'title' => 'Flash Deal Starting Soon!',
            'message' => "{$this->flashDeal->title} is starting soon! Don't miss out on amazing deals.",
            'flash_deal_id' => $this->flashDeal->id,
            'flash_deal_title' => $this->flashDeal->title,
            'start_time' => $this->flashDeal->start_time,
            'end_time' => $this->flashDeal->end_time,
            'action_url' => "/flash-deals/{$this->flashDeal->id}",
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
