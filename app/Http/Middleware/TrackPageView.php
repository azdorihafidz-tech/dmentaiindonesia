<?php

namespace App\Http\Middleware;

use App\Models\PageView;
use App\Services\GeoIpCountryLookup;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackPageView
{
    public function __construct(private readonly GeoIpCountryLookup $countryLookup) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $clientIp = $request->ip();

        if ($request->method() !== 'GET'
            || $response->getStatusCode() !== 200
            || $request->expectsJson()
            || ! $request->hasSession()
            || $request->user()
            || filter_var($clientIp, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false
            || $this->isBot($request->userAgent())) {
            return $response;
        }

        $route = $request->route();
        if (! $route || str_starts_with($route->uri(), 'admin')) {
            return $response;
        }

        $sessionId = $request->session()->getId();
        if (blank($sessionId)) {
            return $response;
        }

        $location = $this->countryLookup->resolve($clientIp);

        PageView::query()->create([
            'path' => mb_substr('/'.ltrim($request->path(), '/'), 0, 255),
            'visitor_hash' => hash_hmac('sha256', $sessionId, (string) config('app.key')),
            'country_code' => $location['country_code'] ?? null,
            'country_name' => $location['country_name'] ?? null,
            'viewed_at' => now(),
        ]);

        return $response;
    }

    private function isBot(?string $userAgent): bool
    {
        return filled($userAgent) && preg_match('/bot|crawl|spider|slurp|facebookexternalhit|whatsapp/i', $userAgent) === 1;
    }
}
