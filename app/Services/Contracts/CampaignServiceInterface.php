<?php

namespace App\Services\Contracts;

use App\Models\Campaign;
use Illuminate\Support\Collection;

interface CampaignServiceInterface
{
    /**
     * Create a new campaign.
     */
    public function createCampaign(array $data): Campaign;

    /**
     * Get target customers for a campaign.
     */
    public function getTargetCustomers(Campaign $campaign): Collection;

    /**
     * Send campaign emails.
     */
    public function sendCampaign(Campaign $campaign): void;

    /**
     * Track email open.
     */
    public function trackEmailOpen(string $trackingId): void;

    /**
     * Get campaign statistics.
     */
    public function getCampaignStats(Campaign $campaign): array;

    /**
     * Schedule a campaign.
     */
    public function scheduleCampaign(Campaign $campaign, \DateTime $scheduledAt): Campaign;
}
