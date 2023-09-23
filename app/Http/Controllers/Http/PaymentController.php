<?php

namespace App\Http\Controllers\Http;

use App\Http\Controllers\Controller;
use App\Models\Product;

class PaymentController extends Controller
{
    /**
     * @param Product $product
     * @return \Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\Foundation\Application
     */
    public function __invoke(Product $product
    ): \Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\Foundation\Application {
        return view('payment', [
            'product' => $product,
        ]);
    }
}
