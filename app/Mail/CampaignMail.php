<?php

namespace App\Mail;

use App\Models\Campaign;
use App\Models\CampaignRecipient;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Campaign $campaign,
        public CampaignRecipient $recipient
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->campaign->subject,
        );
    }

    public function content(): Content
    {
        // Add tracking pixel
        $trackingUrl = route('campaign.track', [
            'recipient' => $this->recipient->id,
            'hash' => hash('sha256', $this->recipient->id . config('app.key')),
        ]);

        return new Content(
            view: 'emails.campaigns.campaign',
            with: [
                'campaign' => $this->campaign,
                'recipient' => $this->recipient,
                'trackingUrl' => $trackingUrl,
                'content' => $this->campaign->content,
            ],
        );
    }
}
