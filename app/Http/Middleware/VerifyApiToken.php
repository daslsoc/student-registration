<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards the integration API. Requires an `Authorization: Bearer <token>`
 * header matching config('integration.api_token'), compared in constant time.
 *
 * Fails closed: if no token is configured, or none is provided, or they don't
 * match, the request is rejected with 401. This is the only thing standing
 * between an anonymous caller and student PII, so it must never be optional.
 */
class VerifyApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $configured = (string) config('integration.api_token');
        $provided = (string) $request->bearerToken();

        if ($configured === '' || $provided === '' || ! hash_equals($configured, $provided)) {
            abort(401, 'Unauthorized.');
        }

        return $next($request);
    }
}
