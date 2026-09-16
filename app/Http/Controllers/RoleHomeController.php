<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class RoleHomeController extends Controller
{
    public function admin(): View
    {
        return view('role-home', ['role' => 'Admin']);
    }

    public function hr(): View
    {
        return view('role-home', ['role' => 'HR']);
    }

    public function employee(): View
    {
        return view('role-home', ['role' => 'Employee']);
    }
}
