<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * Set TRUSTED_PROXIES in .env. Use the load balancer / reverse proxy IPs
     * where possible; '*' is only safe when the app cannot be reached directly,
     * bypassing the proxy, since a client that reaches PHP directly can forge
     * X-Forwarded-For and defeat the per-IP rate limiter.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies;

    public function __construct()
    {
        $proxies = env('TRUSTED_PROXIES');

        if ($proxies !== null && $proxies !== '') {
            $this->proxies = $proxies === '*'
                ? '*'
                : array_map('trim', explode(',', $proxies));
        }
    }

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}
