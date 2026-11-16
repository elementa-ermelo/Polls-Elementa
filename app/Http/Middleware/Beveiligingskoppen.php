<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * CSP met een nonce voor bezoekers (niet ingelogd). Het beheer gebruikt veel onclick-attributen
 * en krijgt daarom geen CSP (site-kwaliteit E06: "beheer is meestal ingelogd en krijgt dan geen CSP").
 * De overige kopregels (HSTS, nosniff, Referrer-Policy, X-Frame-Options) zet nginx.
 */
class Beveiligingskoppen
{
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = base64_encode(random_bytes(16));
        View::share('cspNonce', $nonce);

        $response = $next($request);

        if (! auth()->check() && ! $response->headers->has('Content-Security-Policy')) {
            $response->headers->set('Content-Security-Policy', implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'nonce-{$nonce}'",
                "style-src 'self' 'unsafe-inline'",
                "img-src 'self' data:",
                "font-src 'self'",
                "connect-src 'self'",
                "form-action 'self'",
                "frame-ancestors 'self'",
                "base-uri 'self'",
                "object-src 'none'",
            ]));
        }

        return $response;
    }
}
