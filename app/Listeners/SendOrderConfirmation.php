<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Mail\OrderConfirmation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendOrderConfirmation implements ShouldQueue
{
    public function handle(OrderPlaced $event): void
    {
        $order = $event->order;
        $email = $order->user?->email ?? $order->customer_email;

        if ($email) {
            Mail::to($email)->send(new OrderConfirmation($order));
        }
    }
}
