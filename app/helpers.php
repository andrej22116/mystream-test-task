<?php

use Stripe\StripeClient;

if (!function_exists('stripe_client')) {
    /**
     * Get StripeClient from Container
     * @return StripeClient
     */
    function stripe_client(): StripeClient
    {
        return app(StripeClient::class);
    }
}
