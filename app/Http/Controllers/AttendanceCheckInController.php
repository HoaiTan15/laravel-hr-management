<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class AttendanceCheckInController extends Controller
{
    public function create(): View
    {
        return view('attendance.check-in');
    }
}
