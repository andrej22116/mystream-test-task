<?php

namespace App\Http\Controllers\Api\Stripe\Hook;

use App\Http\Controllers\Controller;
use App\Http\Requests\Stripe\Hook\WebhookRequest;
use Illuminate\Http\Request;

class CheckoutWebhookController extends Controller
{
    private const WEBHOOK_ENV_NAME = 'STRIPE_WEBHOOK_CHECKOUT_SECRETE';

    public function __invoke(WebhookRequest $request)
    {
        $event = $request->getEvent(self::WEBHOOK_ENV_NAME);

        return response(null, 200);
    }
}
