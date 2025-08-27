<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Show the user registration form.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle the registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        // Validate input
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Assign default role
        $user->assignRole('Agent');

        // Trigger event
        event(new Registered($user));

        // Log the user in
        Auth::login($user);

        // Redirect based on role
        if ($user->hasRole('Admin')) {
            return redirect('/admin/dashboard');
        } elseif ($user->hasRole('Manager')) {
            return redirect('/manager/dashboard');
        } elseif ($user->hasRole('Agent')) {
            return redirect('/agent/dashboard');
        }

        // Fallback
        return redirect('/dashboard');
    }
}
