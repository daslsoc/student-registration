<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards the integration API. Requires an `Authorization: Bearer <token>`
 * header matching one of the tokens in config('integration.api_tokens'),
 * compared in constant time.
 *
 * Each consumer app gets its OWN named token, so a key can be rotated or
 * revoked for one app without breaking the others, and so a log line can say
 * WHICH app made a call. The matched consumer's name is attached to the
 * request as `integration_consumer` for exactly that purpose.
 *
 * Fails closed: if no tokens are configured, or none is provided, or none
 * match, the request is rejected with 401. This is the only thing standing
 * between an anonymous caller and family PII, so it must never be optional.
 */
class VerifyApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $provided = (string) $request->bearerToken();
        $consumer = $provided === '' ? null : $this->consumerFor($provided);

        if ($consumer === null) {
            abort(401, 'Unauthorized.');
        }

        // Let controllers / logging know which app is calling.
        $request->attributes->set('integration_consumer', $consumer);

        return $next($request);
    }

    /**
     * The name of the consumer whose token matches, or null if none does.
     *
     * Deliberately checks every configured token rather than returning on the
     * first hit, so how long this takes doesn't reveal how many tokens are
     * configured or which one matched.
     */
    private function consumerFor(string $provided): ?string
    {
        $match = null;

        foreach ((array) config('integration.api_tokens') as $name => $token) {
            $token = (string) $token;

            if ($token !== '' && hash_equals($token, $provided)) {
                $match = (string) $name;
            }
        }

        return $match;
    }
}
