<?php

use App\Http\Controllers\AttendanceCheckInController;
use App\Http\Controllers\AttendanceManagementController;
use App\Http\Controllers\AdminRequestController;
use App\Http\Controllers\EmployeeProfileController;
use App\Http\Controllers\EmployeeRequestController;
use App\Http\Controllers\EmployeeTaskController;
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
    Route::middleware('role:hr,employee')->group(function (): void {
        Route::get('/attendance/check-in', [AttendanceCheckInController::class, 'create'])
            ->name('attendance.check-in');
        Route::post('/attendance/check-in', [AttendanceCheckInController::class, 'store'])->name('attendance.check-in.store');
        Route::get('/attendance/check-in/success', [AttendanceCheckInController::class, 'success'])->name('attendance.check-in.success');
        Route::post('/attendance/check-out', [AttendanceCheckInController::class, 'checkout'])->name('attendance.check-out');
        Route::get('/attendance/check-out/success', [AttendanceCheckInController::class, 'checkoutSuccess'])->name('attendance.check-out.success');
    });

    Route::prefix('admin')->middleware('role:admin')->group(function (): void {
        Route::get('/', [RoleHomeController::class, 'admin'])->name('admin.home');
        Route::get('/requests', [AdminRequestController::class, 'index'])->name('admin.requests.index');
        Route::get('/requests/{requestModel}', [AdminRequestController::class, 'show'])->name('admin.requests.show');
        Route::put('/requests/{requestModel}', [AdminRequestController::class, 'process'])->name('admin.requests.process');
    });

    Route::prefix('hr')->middleware('role:hr')->group(function (): void {
        Route::get('/', [RoleHomeController::class, 'hr'])->name('hr.home');
        Route::get('/attendance', [AttendanceManagementController::class, 'index'])->name('hr.attendance.index');
        Route::get('/attendance/{attendance}', [AttendanceManagementController::class, 'show'])->name('hr.attendance.show');
        Route::put('/attendance/{attendance}', [AttendanceManagementController::class, 'update'])->name('hr.attendance.update');
    });

    Route::prefix('employee')->middleware('role:employee')->group(function (): void {
        Route::get('/', [RoleHomeController::class, 'employee'])->name('employee.home');

        Route::middleware('employee.checked.in')->group(function (): void {
            Route::get('/tasks', [EmployeeTaskController::class, 'index'])->name('employee.tasks.index');
            Route::get('/tasks/{task}', [EmployeeTaskController::class, 'show'])->name('employee.tasks.show');
            Route::put('/tasks/{task}/status', [EmployeeTaskController::class, 'updateStatus'])->name('employee.tasks.status');
            Route::get('/profile', [EmployeeProfileController::class, 'show'])->name('employee.profile');
            Route::put('/profile', [EmployeeProfileController::class, 'update'])->name('employee.profile.update');
            Route::post('/profile/change-request', [EmployeeProfileController::class, 'requestChange'])->name('employee.profile.change-request');
            Route::put('/profile/change-request/{requestModel}', [EmployeeProfileController::class, 'updateChangeRequest'])->name('employee.profile.change-request.update');
            Route::get('/requests', [EmployeeRequestController::class, 'index'])->name('employee.requests.index');
            Route::get('/requests/create', [EmployeeRequestController::class, 'create'])->name('employee.requests.create');
            Route::post('/requests', [EmployeeRequestController::class, 'store'])->name('employee.requests.store');
            Route::get('/requests/{requestModel}', [EmployeeRequestController::class, 'show'])->name('employee.requests.show');
            Route::get('/requests/{requestModel}/edit', [EmployeeRequestController::class, 'edit'])->name('employee.requests.edit');
            Route::put('/requests/{requestModel}', [EmployeeRequestController::class, 'update'])->name('employee.requests.update');
            Route::delete('/requests/{requestModel}', [EmployeeRequestController::class, 'destroy'])->name('employee.requests.destroy');
        });
    });
});
