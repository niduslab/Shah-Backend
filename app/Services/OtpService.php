<?php

namespace App\Services;

use App\Models\PasswordResetOtp;
use App\Models\User;
use App\Mail\OtpMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class OtpService
{
    /**
     * Generate and send OTP to email.
     */
    public function sendOtp(string $email): array
    {
        // Check if user exists
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            // Return success anyway to prevent email enumeration
            return [
                'success' => true,
                'message' => 'If that email exists, an OTP has been sent.',
            ];
        }

        // Delete old OTPs for this email
        PasswordResetOtp::where('email', $email)->delete();

        // Generate 6-digit OTP
        $otp = $this->generateOtp();

        // Store OTP in database
        PasswordResetOtp::create([
            'email' => $email,
            'otp' => $otp,
            'expires_at' => Carbon::now()->addMinutes(5), // 5 minutes expiry
            'is_used' => false,
        ]);

        // Send OTP via email
        try {
            Mail::to($email)->send(new OtpMail($otp));
        } catch (\Exception $e) {
            \Log::error('Failed to send OTP email: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.',
            ];
        }

        return [
            'success' => true,
            'message' => 'OTP sent to your email. Valid for 5 minutes.',
        ];
    }

    /**
     * Generate and send OTP for new user registration.
     */
    public function sendRegistrationOtp(string $email): array
    {
        // Check if user already exists
        if (User::where('email', $email)->exists()) {
             return [
                'success' => false,
                'message' => 'User with this email already exists.',
            ];
        }

        // Delete old OTPs for this email
        PasswordResetOtp::where('email', $email)->delete();

        // Generate 6-digit OTP
        $otp = $this->generateOtp();

        // Store OTP in database
        PasswordResetOtp::create([
            'email' => $email,
            'otp' => $otp,
            'expires_at' => Carbon::now()->addMinutes(5), // 5 minutes expiry
            'is_used' => false,
        ]);

        // Send OTP via email
        try {
            Mail::to($email)->send(new OtpMail($otp, 'registration'));
        } catch (\Exception $e) {
            \Log::error('Failed to send registration OTP email: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.',
            ];
        }

        return [
            'success' => true,
            'message' => 'OTP sent to your email. Valid for 5 minutes.',
        ];
    }

    /**
     * Verify OTP code.
     */
    public function verifyOtp(string $email, string $otp): array
    {
        $otpRecord = PasswordResetOtp::forEmail($email)
            ->where('otp', $otp)
            ->latest()
            ->first();

        if (!$otpRecord) {
            return [
                'success' => false,
                'message' => 'Invalid OTP code.',
            ];
        }

        if ($otpRecord->is_used) {
            return [
                'success' => false,
                'message' => 'OTP has already been used.',
            ];
        }

        if ($otpRecord->isExpired()) {
            return [
                'success' => false,
                'message' => 'OTP has expired. Please request a new one.',
            ];
        }

        return [
            'success' => true,
            'message' => 'OTP verified successfully.',
            'otp_id' => $otpRecord->id,
        ];
    }

    /**
     * Reset password with OTP.
     */
    public function resetPasswordWithOtp(string $email, string $otp, string $password): array
    {
        // Verify OTP first
        $verification = $this->verifyOtp($email, $otp);
        
        if (!$verification['success']) {
            return $verification;
        }

        // Find user
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found.',
            ];
        }

        // Update password
        DB::beginTransaction();
        try {
            $user->update([
                'password' => bcrypt($password),
                'remember_token' => \Str::random(60),
            ]);

            // Mark OTP as used
            $otpRecord = PasswordResetOtp::find($verification['otp_id']);
            $otpRecord->markAsUsed();

            // Revoke all existing tokens
            $user->tokens()->delete();

            DB::commit();

            return [
                'success' => true,
                'message' => 'Password reset successfully.',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Password reset failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Failed to reset password. Please try again.',
            ];
        }
    }

    /**
     * Generate random 6-digit OTP.
     */
    protected function generateOtp(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Clean up expired OTPs (can be run via scheduled task).
     */
    public function cleanupExpiredOtps(): int
    {
        return PasswordResetOtp::where('expires_at', '<', Carbon::now())
            ->orWhere('is_used', true)
            ->delete();
    }
}
