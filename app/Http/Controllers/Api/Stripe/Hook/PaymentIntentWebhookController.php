<?php

namespace App\Http\Controllers\Api\Stripe\Hook;

use App\Http\Controllers\Controller;
use App\Http\Requests\Stripe\Hook\WebhookRequest;
use App\Models\Stripe\PaymentIntent;
use App\Models\Stripe\PaymentIntentStatus;
use App\Services\Stripe\PaymentService;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;

class PaymentIntentWebhookController extends Controller
{
    private const WEBHOOK_ENV_NAME = 'STRIPE_WEBHOOK_PAYMENT_INTENT_SECRETE';

    public function __invoke(PaymentService $paymentService, WebhookRequest $request)
    {
        try {
            $event = $request->getEvent(self::WEBHOOK_ENV_NAME);
        } catch (SignatureVerificationException $e) {
            Log::error($e->getMessage(), [
                'exception' => $e
            ]);

            return response(null, 200);
        }

        try {
            $paymentService->processPaymentIntentEvent($event);
        }
        catch (\Throwable $e) {
            Log::error($e->getMessage());

            return response(null, 500);
        }

        return response(null, 200);
    }
}
