<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
 public function store(LoginRequest $request): RedirectResponse
{
    $request->authenticate();
    $request->session()->regenerate();

    $user = Auth::user();
    $role = $user->getRoleNames()->first(); // e.g., 'Admin', 'Manager', 'Agent'

    switch ($role) {
        case 'Admin':
            return redirect()->route('admin.dashboard');
        case 'Manager':
            return redirect()->route('manager.dashboard');
        case 'Agent':
            return redirect()->route('agent.dashboard');
        default:
            Auth::logout(); // Invalid role
            return redirect('/login')->withErrors(['role' => 'Unauthorized role.']);
    }
}


    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
{
    Auth::guard('web')->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/login'); // Redirect to login after logout
}

}
