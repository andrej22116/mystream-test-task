<?php

namespace App\Services\Stripe;

use App\Models\Product;
use Stripe\Checkout\Session;
use Stripe\StripeClient;

class StripeCheckoutSession
{
    public function __construct(protected StripeClient $stripe)
    {
    }

    public function createSession(iterable $productList, array $config = []): Session
    {
        $config = array_merge([
            'mode' => 'payment',
            'success_url' => route('http.buy.success'),
            'cancel_url' => route('http.buy.canceled'),
        ], $config);

        $productList = collect($productList)->map(fn(Product $product) => StripeProduct::makeArray($product));

        $session = $this->stripe->checkout->sessions->create(
            array_merge(
                ['line_items' => $productList->all(),],
                $config
            )
        );

        return $session;
    }

    /**
     * Save/Update the session in the database
     * @param Session $session
     * @return void
     */
    protected function commitSession(Session $session): void
    {

    }
}
