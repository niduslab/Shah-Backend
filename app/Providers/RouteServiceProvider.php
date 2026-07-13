<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * Limits are keyed by authenticated user id when available, falling back to
     * the client IP. The IP fallback is only correct when TrustProxies is
     * configured, otherwise every request behind the proxy shares one bucket.
     */
    protected function configureRateLimiting(): void
    {
        // Default for any route that does not opt into a more specific limiter.
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(120)->by($this->resolveRequestSignature($request));
        });

        // Public storefront reads. A single page view fans out into many of
        // these, so the ceiling is high enough to absorb browsing bursts.
        RateLimiter::for('public-read', function (Request $request) {
            return Limit::perMinute(300)->by($this->resolveRequestSignature($request));
        });

        // Fire-and-forget analytics beacons: high volume, cheap, low value to an
        // attacker. Kept separate so they can never starve the catalog budget.
        RateLimiter::for('telemetry', function (Request $request) {
            return Limit::perMinute(300)->by($this->resolveRequestSignature($request));
        });

        // Credential and OTP endpoints. Tight, and always keyed by IP so that
        // an attacker cannot spread attempts across accounts.
        RateLimiter::for('auth', function (Request $request) {
            return [
                Limit::perMinute(10)->by($request->ip()),
                Limit::perMinute(5)->by($request->input('email') . '|' . $request->ip()),
            ];
        });

        // Order placement and payment retries: money-moving, low legitimate rate.
        RateLimiter::for('checkout', function (Request $request) {
            return Limit::perMinute(20)->by($this->resolveRequestSignature($request));
        });

        // Unauthenticated public writes (contact, newsletter, popups) — the usual
        // spam targets.
        RateLimiter::for('public-write', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        // Authenticated staff working a dashboard generate steady, trusted load.
        RateLimiter::for('admin', function (Request $request) {
            return Limit::perMinute(600)->by($this->resolveRequestSignature($request));
        });
    }

    /**
     * Key rate limits by user when authenticated, otherwise by client IP.
     */
    protected function resolveRequestSignature(Request $request): string
    {
        return $request->user()?->id
            ? 'user:' . $request->user()->id
            : 'ip:' . $request->ip();
    }
}
