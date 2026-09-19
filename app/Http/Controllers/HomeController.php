<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        if ($request->user()?->role === UserRole::ADMIN) {
            return redirect()->route('admin.home');
        }

        if ($request->user()?->role === UserRole::HR) {
            return redirect()->route('hr.home');
        }

        if ($request->user()?->role === UserRole::EMPLOYEE) {
            return redirect()->route('employee.home');
        }

        return view('welcome');
    }
}
