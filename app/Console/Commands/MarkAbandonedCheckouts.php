<?php

namespace App\Console\Commands;

use App\Services\AnalyticsService;
use Illuminate\Console\Command;

class MarkAbandonedCheckouts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analytics:mark-abandoned-checkouts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark inactive checkouts as abandoned';

    /**
     * Execute the console command.
     */
    public function handle(AnalyticsService $analyticsService)
    {
        $this->info('Marking abandoned checkouts...');
        
        $analyticsService->markAbandonedCheckouts();
        
        $this->info('Abandoned checkouts marked successfully!');
        
        return 0;
    }
}
