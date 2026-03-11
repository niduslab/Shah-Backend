<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    public $purpose;

    public function __construct($otp, $purpose = 'password_reset')
    {
        $this->otp = $otp;
        $this->purpose = $purpose;
    }

    public function build()
    {
        $subject = $this->purpose === 'password_reset' 
            ? 'Password Reset OTP' 
            : 'Your OTP for Email Verification';
            
        return $this->subject($subject)
                    ->view('emails.otp');
    }
}


