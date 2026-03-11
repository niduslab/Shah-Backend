<?php

namespace App\Console\Commands;

use App\Services\OtpService;
use Illuminate\Console\Command;

class CleanupExpiredOtps extends Command
{
    protected $signature = 'otp:cleanup';
    protected $description = 'Clean up expired and used OTPs';

    public function __construct(
        protected OtpService $otpService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $deleted = $this->otpService->cleanupExpiredOtps();
        
        $this->info("Cleaned up {$deleted} expired/used OTPs.");
        
        return 0;
    }
}
