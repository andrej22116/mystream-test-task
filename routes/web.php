<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', 'HomePageController')->name('http.home');

Route::get('/buy/finish', 'PaymentResultController')->name('http.buy.result');

Route::get('/buy/error', function () {
    return view('payment.error');
})->name('http.buy.error');

Route::get('/buy/{product}', 'PaymentController')->name('http.buy');
