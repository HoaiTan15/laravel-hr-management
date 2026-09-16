<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\User;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');
        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! $user->is_active || ! Auth::attempt($credentials, $remember)) {
            return back()->withErrors([
                'email' => 'The provided credentials are invalid or the account is inactive.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->route(match ($user->role) {
            UserRole::ADMIN => 'admin.home',
            UserRole::HR => 'hr.home',
            UserRole::EMPLOYEE => 'employee.home',
        });
    }
}
