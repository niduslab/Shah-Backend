<?php

namespace App\Events;

use App\Models\ProductReturn;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReturnCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ProductReturn $return
    ) {}
}
