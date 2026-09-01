<?php

namespace Tests\Support;

use Stripe\HttpClient\ClientInterface;

/**
 * Offline stand-in for Stripe's HTTP client so feature tests can exercise the
 * real registration/update code paths (which create Checkout sessions) without
 * network access or a live API key.
 *
 * Install it in a test with:
 *   config(['services.stripe.secret' => 'sk_test_fake']);
 *   \Stripe\ApiRequestor::setHttpClient(new \Tests\Support\FakeStripeHttpClient);
 */
class FakeStripeHttpClient implements ClientInterface
{
    public function request($method, $absUrl, $headers, $params, $hasFile, $apiMode = 'v1', $maxNetworkRetries = null)
    {
        // Minimal Checkout Session shape: the controller only reads ->url.
        $body = json_encode([
            'id' => 'cs_test_fake',
            'object' => 'checkout.session',
            'url' => 'https://checkout.stripe.com/c/pay/cs_test_fake',
            'payment_status' => 'unpaid',
            'status' => 'open',
        ]);

        return [$body, 200, []];
    }
}
