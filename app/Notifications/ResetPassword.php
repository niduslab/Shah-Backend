<?php

// app/Notifications/ResetPassword.php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPassword extends BaseResetPassword
{
    protected function getResetUrl($notifiable)
    {
        // Customize the reset URL with token and email
        return url(config('app.url').':3000/reset-password?token='.$this->token.'&email='.$notifiable->getEmailForPasswordReset());
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Reset Password Notification')
                    ->line('You are receiving this email because we received a password reset request for your account.')
                    ->action('Reset Password', $this->getResetUrl($notifiable))
                    ->line('If you did not request a password reset, no further action is required.');
    }
}
