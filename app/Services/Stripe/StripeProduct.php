<?php

namespace App\Services\Stripe;

use App\Models\Product;

class StripeProduct
{
    /**
     * @param Product $product
     * @param int $amount
     */
    public function __construct(protected Product $product, protected int $amount = 1)
    {
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'price_data' => [
                'currency' => 'usd',
                'product_data' => [
                    'name' => $this->product->title,
                    'description' => $this->product->description,
                ],
                'unit_amount' => $this->product->integer_price,
            ],
            'quantity' => $this->amount,
        ];
    }

    /**
     * @param Product $product
     * @param int $amount
     * @return array
     */
    public static function makeArray(Product $product, int $amount = 1): array
    {
        return (new static($product, $amount))->toArray();
    }
}
