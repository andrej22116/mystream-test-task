<?php

use Illuminate\Support\Facades\Route;

/*----------------------------------------------------------------------------------*
 |                                                                                  |
                            STRIPE HOOK API/V1/STRIPE/HOOK
 |                                                                                  |
 *----------------------------------------------------------------------------------*/
Route::group(['prefix' => 'hook'], function () {

    // CHECKOUT WEBHOOK
    Route::post('checkout', \App\Http\Controllers\Api\Stripe\Hook\CheckoutWebhookController::class)
        ->name('api.v1.stripe.hook.checkout');

    // PAYMENT INTENT STATUS
    Route::post('payment-intent', \App\Http\Controllers\Api\Stripe\Hook\PaymentIntentWebhookController::class)
        ->name('api.v1.stripe.hook.payment_intent');
});
