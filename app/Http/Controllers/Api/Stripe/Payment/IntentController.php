<?php

namespace App\Http\Controllers\Api\Stripe\Payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Stripe\Payment\Intent\CreateIntentRequest;
use App\Services\Stripe\PaymentService;
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
    public function create(PaymentService $paymentService, CreateIntentRequest $request): \Illuminate\Http\JsonResponse
    {
        $product = $request->getProduct();

        try {
            $paymentIntent = $paymentService->newPayment($product);
        } catch (ApiErrorException $e) {
            return response()->json([
                'status' => 'error',
                'code' => $e->getCode(),
                'client_secret' => $e->getMessage(),
            ]);
        } catch (\Throwable $e) {
            Log::error($e->getMessage(), [
                'exception' => $e,
            ]);

            return response()->json([
                'status' => 'error',
                'code' => $e->getCode(),
                'client_secret' => '',
            ]);
        }

        return response()->json([
            'status' => 'ok',
            'client_secret' => $paymentIntent->client_secret,
        ]);
    }
}
