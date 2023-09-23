<?php

namespace App\Http\Controllers\Http;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomePageController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        $products = Product::all();

        $groupedProducts = $products->groupBy('payment');

        return view('index', [
            'grouped_products' => $groupedProducts,
        ]);
    }
}
