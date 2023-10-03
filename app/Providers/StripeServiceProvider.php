<?php

namespace App\Providers;

use App\Exceptions\Stripe\SecreteKeyNotExists;
use App\Services\Stripe\PaymentService;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Stripe\StripeClient;

class StripeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singletonIf(StripeClient::class, function (Application $app) {
            $apiKey = env('STRIPE_API_PRIVATE_KEY');
            if (!$apiKey) {
                throw new SecreteKeyNotExists();
            }

            return new StripeClient([
                'api_key' => $apiKey,
            ]);
        });

        $this->app->bind(PaymentService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
