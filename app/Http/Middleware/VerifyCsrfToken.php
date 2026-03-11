<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        '/login/check-auth',
        '/api/login',
        '/api/auth/login',
        '/api/auth/register',
        '/api/auth/google/callback',
        '/api/auth/forgot-password',
        '/api/auth/reset-password',
        '/api/auth/send-otp',
        '/api/auth/verify-otp',
        '/api/auth/reset-password-otp',
        '/api/payments/ssl-commerz/*',
        '/api/checkout/*',
        // 'login'
    ];
}
