<?php

namespace App\Http\Controllers\Http;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class RegistrationController extends Controller
{
    public function page()
    {
        return view('auth.registration');
    }

    /**
     * Handle the incoming request.
     */
    public function register(Request $request)
    {
        $credentials = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials['password'] = Hash::make($credentials['password']);

        try {
            /** @var User $user */
            $user = User::create($credentials);

            auth()->login($user);
        }
        catch (\Throwable $e)
        {
            return back();
        }

        return redirect()->intended(route('http.home'));
    }
}
