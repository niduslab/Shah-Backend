# API Rate Limiting

## Background

The API previously ran on Laravel's stock limiter: **60 requests/minute shared
across every endpoint**, applied globally in the `api` middleware group. A single
storefront page view fans out into 8–15 API calls (catalog, brands, categories,
banners, cart, analytics), so a visitor browsing normally exhausted the entire
budget in a few page views and everything started returning `429`.

Rate limiting is now **tiered per route group**. Each tier gets its own budget, so
cheap catalog reads can't starve checkout, and analytics beacons can't starve the
catalog.

## Tiers

Defined in `app/Providers/RouteServiceProvider::configureRateLimiting()`.

| Limiter        | Limit    | Keyed by      | Applies to |
|----------------|----------|---------------|------------|
| `public-read`  | 300/min  | user id or IP | Catalog, brands, categories, pages, policies, banners, galleries, flash deals, cart pricing, public reviews, order tracking, payment status |
| `telemetry`    | 300/min  | user id or IP | `analytics/track/*` beacons |
| `api`          | 120/min  | user id or IP | Authenticated customer routes (default fallback) |
| `admin`        | 600/min  | user id       | Admin panel (dashboards fan out into many parallel reads) |
| `checkout`     | 20/min   | user id or IP | Checkout, payment retry, pre-order balance |
| `auth`         | 10/min per IP + 5/min per email+IP | IP | Login, register, password reset, OTP |
| `public-write` | 10/min   | IP            | Contact form, newsletter, visitor popup |

**Exempt (deliberately):** the four `payments/ssl-commerz/*` gateway callbacks.
SSLCommerz retries IPNs from its own IPs; a `429` there would drop a payment
notification for a real order. Authenticity is enforced by signature validation in
`PaymentController`, not by rate limiting. The browser-polled
`payments/{orderNumber}/status` route is **not** exempt — it stays on `public-read`
so the exemption can't be used to enumerate order numbers.

## Required: `TRUSTED_PROXIES`

**This is not optional in production.** Rate limits fall back to keying on the
client IP for guests. If the app sits behind Nginx, a load balancer, or Cloudflare
and `TRUSTED_PROXIES` is unset, Laravel reads the *proxy's* IP for every request —
so **every visitor on the site shares a single bucket**, and the limits above will
trip almost immediately under real traffic. This was a live bug: `TrustProxies`
shipped with `$proxies = null`.

Set in `.env`:

```dotenv
# Comma-separated proxy IPs (preferred):
TRUSTED_PROXIES=10.0.0.1,10.0.0.2

# Or, only when the app is NOT directly reachable except through the proxy:
TRUSTED_PROXIES=*
```

`*` is only safe when PHP cannot be reached directly, bypassing the proxy. A client
that reaches the app directly can forge `X-Forwarded-For` and defeat the per-IP
limiter entirely.

Verify real client IPs are arriving by checking that two different machines get
independent budgets, or by logging `$request->ip()` on any endpoint.

## Note on the cache driver

Counters are stored in the cache. `CACHE_DRIVER=file` (the current setting) keeps
them on local disk, which means **each web server counts independently** — with N
app servers, the effective limit is N× the configured number. Fine on a single
server. If you scale out, move to a shared store:

```dotenv
CACHE_DRIVER=redis
```

## Nested throttles: a gotcha

When two throttle middlewares stack (a route group inside another throttled group),
**the outer one wins and the inner, stricter limit is silently ignored**. Drop the
parent's limiter explicitly:

```php
Route::middleware('throttle:checkout')
    ->withoutMiddleware('throttle:api')
    ->group(function () { /* ... */ });
```

This bit the payment-retry routes during this change. Audit with:

```bash
php artisan route:list --path=api --json
```

and check that no route resolves to more than one `ThrottleRequests:*` entry.

## Tuning

Adjust the numbers in `configureRateLimiting()`. When raising a limit, prefer
raising only the specific tier under pressure rather than the global default — the
whole point of the split is that a noisy tier can't take the others down with it.
Clients should honour the `Retry-After` and `X-RateLimit-Remaining` response
headers that Laravel sets automatically on throttled routes.
