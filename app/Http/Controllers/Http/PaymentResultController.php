<?php

namespace App\Http\Controllers\Http;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class PaymentResultController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(StripeClient $stripe, Request $request)
    {
        $clientIntentSecret = $request->get('payment_intent');
        if ( !$clientIntentSecret ) {
            return redirect(route('http.home'));
        }

        try {
            $paymentIntent = $stripe->paymentIntents->retrieve($clientIntentSecret);
        } catch (ApiErrorException $e) {
            Log::error($e->getMessage(), [
                'exception' => $e
            ]);
            return redirect(route('http.home'));
        }

        Log::debug($paymentIntent);

        return view('payment.success');
    }
}
