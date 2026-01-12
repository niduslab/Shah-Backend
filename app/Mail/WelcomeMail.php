<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to Shah Sports!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.auth.welcome',
            with: [
                'user' => $this->user,
                'shopUrl' => config('app.frontend_url'),
            ],
        );
    }
}
