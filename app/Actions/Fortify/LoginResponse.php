<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\Request;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = $request->user();

        if ($user->hasRole('Admin')) {
            return redirect('/admin/dashboard');
        } elseif ($user->hasRole('Manager')) {
            return redirect('/manager/dashboard');
        } elseif ($user->hasRole('Agent')) {
            return redirect('/agent/dashboard');
        }

        return redirect('/dashboard'); // fallback
    }
}
