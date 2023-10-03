<?php

namespace App\Http\Controllers\Http;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): \Illuminate\Http\RedirectResponse
    {
        auth()->logout();

        return redirect()->intended(route('http.home'));
    }
}
