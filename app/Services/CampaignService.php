<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\User;
use App\Services\Contracts\CampaignServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CampaignService implements CampaignServiceInterface
{
    /**
     * Create a new campaign.
     * 
     * @param array $data
     * @return Campaign
     */
    public function createCampaign(array $data): Campaign
    {
        return Campaign::create([
            'name' => $data['name'],
            'subject' => $data['subject'],
            'content' => $data['content'],
            'campaign_type' => $data['campaign_type'],
            'target_type' => $data['target_type'] ?? 'all_customers',
            'target_criteria' => $data['target_criteria'] ?? null,
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'status' => $data['scheduled_at'] ? 'scheduled' : 'draft',
        ]);
    }

    /**
     * Get target customers for a campaign.
     * 
     * @param Campaign $campaign
     * @return Collection
     */
    public function getTargetCustomers(Campaign $campaign): Collection
    {
        $query = User::customers()->active();

        switch ($campaign->target_type) {
            case 'all_customers':
                // All active customers
                break;

            case 'specific_customers':
                // Filter by specific user IDs
                if (isset($campaign->target_criteria['user_ids'])) {
                    $query->whereIn('id', $campaign->target_criteria['user_ids']);
                }
                break;

            case 'customer_group':
                // Filter by criteria
                $criteria = $campaign->target_criteria ?? [];

                // Filter by purchase history
                if (isset($criteria['min_orders'])) {
                    $query->whereHas('orders', function ($q) use ($criteria) {
                        $q->where('status', 'delivered');
                    }, '>=', $criteria['min_orders']);
                }

                // Filter by total spent
                if (isset($criteria['min_spent'])) {
                    $query->whereHas('orders', function ($q) use ($criteria) {
                        $q->where('status', 'delivered')
                            ->havingRaw('SUM(total_amount) >= ?', [$criteria['min_spent']]);
                    });
                }

                // Filter by registration date
                if (isset($criteria['registered_after'])) {
                    $query->where('created_at', '>=', $criteria['registered_after']);
                }

                // Filter by last order date
                if (isset($criteria['last_order_after'])) {
                    $query->whereHas('orders', function ($q) use ($criteria) {
                        $q->where('created_at', '>=', $criteria['last_order_after']);
                    });
                }
                break;
        }

        return $query->get();
    }

    /**
     * Send campaign emails.
     * 
     * @param Campaign $campaign
     * @return void
     */
    public function sendCampaign(Campaign $campaign): void
    {
        if (!in_array($campaign->status, ['draft', 'scheduled'])) {
            throw new \InvalidArgumentException('Campaign has already been sent.');
        }

        $campaign->update(['status' => 'sending']);

        $customers = $this->getTargetCustomers($campaign);
        $campaign->update(['total_recipients' => $customers->count()]);

        // Create recipients and queue emails
        foreach ($customers as $customer) {
            $recipient = CampaignRecipient::create([
                'campaign_id' => $campaign->id,
                'user_id' => $customer->id,
                'email' => $customer->email,
                'status' => 'pending',
            ]);

            // Queue email sending
            $this->queueCampaignEmail($campaign, $recipient);
        }

        $campaign->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    /**
     * Track email open.
     * 
     * @param string $trackingId
     * @return void
     */
    public function trackEmailOpen(string $trackingId): void
    {
        $recipient = CampaignRecipient::where('id', $trackingId)->first();

        if ($recipient && $recipient->status !== 'opened') {
            $recipient->update([
                'status' => 'opened',
                'opened_at' => now(),
            ]);

            $recipient->campaign->increment('total_opened');
        }
    }

    /**
     * Track email click.
     * 
     * @param string $trackingId
     * @return void
     */
    public function trackEmailClick(string $trackingId): void
    {
        $recipient = CampaignRecipient::where('id', $trackingId)->first();

        if ($recipient && $recipient->status !== 'clicked') {
            $recipient->update([
                'status' => 'clicked',
                'clicked_at' => now(),
            ]);

            $recipient->campaign->increment('total_clicked');
        }
    }

    /**
     * Get campaign statistics.
     * 
     * @param Campaign $campaign
     * @return array
     */
    public function getCampaignStats(Campaign $campaign): array
    {
        $total = $campaign->total_recipients;
        $sent = $campaign->total_sent;
        $opened = $campaign->total_opened;
        $clicked = $campaign->total_clicked;

        return [
            'total_recipients' => $total,
            'total_sent' => $sent,
            'total_opened' => $opened,
            'total_clicked' => $clicked,
            'open_rate' => $sent > 0 ? round(($opened / $sent) * 100, 2) : 0,
            'click_rate' => $opened > 0 ? round(($clicked / $opened) * 100, 2) : 0,
            'delivery_rate' => $total > 0 ? round(($sent / $total) * 100, 2) : 0,
            'status' => $campaign->status,
            'sent_at' => $campaign->sent_at,
        ];
    }

    /**
     * Schedule a campaign.
     * 
     * @param Campaign $campaign
     * @param \DateTime $scheduledAt
     * @return Campaign
     */
    public function scheduleCampaign(Campaign $campaign, \DateTime $scheduledAt): Campaign
    {
        $campaign->update([
            'scheduled_at' => $scheduledAt,
            'status' => 'scheduled',
        ]);

        return $campaign->fresh();
    }

    /**
     * Cancel a scheduled campaign.
     * 
     * @param Campaign $campaign
     * @return Campaign
     */
    public function cancelCampaign(Campaign $campaign): Campaign
    {
        if ($campaign->status !== 'scheduled') {
            throw new \InvalidArgumentException('Only scheduled campaigns can be cancelled.');
        }

        $campaign->update(['status' => 'cancelled']);

        return $campaign->fresh();
    }

    /**
     * Queue campaign email for sending.
     * 
     * @param Campaign $campaign
     * @param CampaignRecipient $recipient
     * @return void
     */
    protected function queueCampaignEmail(Campaign $campaign, CampaignRecipient $recipient): void
    {
        // In production, this would dispatch a job to the queue
        // For now, we'll send directly (should be queued in production)
        try {
            $trackingPixel = route('campaign.track', ['id' => $recipient->id]);
            
            $content = $this->processContent($campaign->content, $recipient, $trackingPixel);

            Mail::send([], [], function ($message) use ($campaign, $recipient, $content) {
                $message->to($recipient->email)
                    ->subject($campaign->subject)
                    ->html($content);
            });

            $recipient->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            $campaign->increment('total_sent');
        } catch (\Exception $e) {
            $recipient->update(['status' => 'bounced']);
            \Log::error('Campaign email failed', [
                'campaign_id' => $campaign->id,
                'recipient_id' => $recipient->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Process email content with personalization.
     * 
     * @param string $content
     * @param CampaignRecipient $recipient
     * @param string $trackingPixel
     * @return string
     */
    protected function processContent(string $content, CampaignRecipient $recipient, string $trackingPixel): string
    {
        $user = $recipient->user;

        // Replace placeholders
        $replacements = [
            '{{first_name}}' => $user?->first_name ?? 'Customer',
            '{{last_name}}' => $user?->last_name ?? '',
            '{{email}}' => $recipient->email,
        ];

        $content = str_replace(array_keys($replacements), array_values($replacements), $content);

        // Add tracking pixel
        $content .= "<img src=\"{$trackingPixel}\" width=\"1\" height=\"1\" style=\"display:none;\" />";

        return $content;
    }

    /**
     * Get all campaigns.
     * 
     * @param array $filters
     * @return Collection
     */
    public function getCampaigns(array $filters = []): Collection
    {
        $query = Campaign::query();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['type'])) {
            $query->where('campaign_type', $filters['type']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }
}
