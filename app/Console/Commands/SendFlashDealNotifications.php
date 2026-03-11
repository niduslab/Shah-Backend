<?php

namespace App\Console\Commands;

use App\Models\FlashDeal;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendFlashDealNotifications extends Command
{
    protected $signature = 'notifications:flash-deals';
    protected $description = 'Send notifications for upcoming flash deals';

    public function __construct(
        protected NotificationService $notificationService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        // Get flash deals starting in the next 30 minutes
        $upcomingDeals = FlashDeal::where('status', 'active')
            ->where('start_time', '>', Carbon::now())
            ->where('start_time', '<=', Carbon::now()->addMinutes(30))
            ->whereDoesntHave('notificationsSent') // Prevent duplicate notifications
            ->get();

        foreach ($upcomingDeals as $deal) {
            $this->notificationService->notifyFlashDealStarting($deal);
            $this->info("Sent notifications for flash deal: {$deal->title}");
        }

        $this->info('Flash deal notifications sent successfully.');
        return 0;
    }
}
