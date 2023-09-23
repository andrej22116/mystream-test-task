<?php

use Illuminate\Support\Facades\Route;

/*----------------------------------------------------------------------------------*
 |                                                                                  |
                               STRIPE API API/V1/STRIPE
 |                                                                                  |
 *----------------------------------------------------------------------------------*/
Route::group(['prefix' => 'stripe'], function () {

    // API/V1/STRIPE/HOOK
    require_once 'hook.php';

    Route::post('payment/intent', [\App\Http\Controllers\Api\Stripe\Payment\IntentController::class, 'create']);
});
