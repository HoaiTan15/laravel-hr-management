<?php

use App\Http\Controllers\AttendanceCheckInController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\RoleHomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', LogoutController::class)
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'active.user'])->group(function (): void {
    Route::get('/attendance/check-in', [AttendanceCheckInController::class, 'create'])
        ->name('attendance.check-in');

    Route::prefix('admin')->middleware('role:admin')->group(function (): void {
        Route::get('/', [RoleHomeController::class, 'admin'])->name('admin.home');
    });

    Route::prefix('hr')->middleware('role:hr')->group(function (): void {
        Route::get('/', [RoleHomeController::class, 'hr'])->name('hr.home');
    });

    Route::prefix('employee')->middleware(['role:employee', 'employee.checked.in'])->group(function (): void {
        Route::get('/', [RoleHomeController::class, 'employee'])->name('employee.home');
    });
});
