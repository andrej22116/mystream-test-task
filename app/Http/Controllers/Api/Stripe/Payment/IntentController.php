<?php

namespace App\Http\Controllers\Api\Stripe\Payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Stripe\Payment\Intent\CreateIntentRequest;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class IntentController extends Controller
{
    /**
     * @param StripeClient $stripeClient
     * @param CreateIntentRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(StripeClient $stripeClient, CreateIntentRequest $request): \Illuminate\Http\JsonResponse
    {
        $product = $request->getProduct();


        try {
            $paymentIntent = $stripeClient->paymentIntents->create([
                'amount' => $product->integer_price,
                'currency' => 'usd',
                'setup_future_usage' => 'on_session',
                'use_stripe_sdk' => true,
            ]);
        } catch (ApiErrorException $e) {
            Log::error($e->getMessage(), [
                'exception' => $e,
            ]);

            return response()->json([
                'status' => 'error',
                'code' => $e->getCode(),
                'client_secret' => $e->getMessage(),
            ]);
        }


        return response()->json([
            'status' => 'ok',
            'client_secret' => $paymentIntent->client_secret,
        ]);
    }
}
