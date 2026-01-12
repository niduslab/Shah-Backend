<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Mail\OrderStatusUpdate;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendOrderStatusUpdate implements ShouldQueue
{
    public function handle(OrderStatusChanged $event): void
    {
        $order = $event->order;
        $email = $order->user?->email ?? $order->customer_email;

        if ($email) {
            Mail::to($email)->send(new OrderStatusUpdate($order, $event->previousStatus));
        }
    }
}
