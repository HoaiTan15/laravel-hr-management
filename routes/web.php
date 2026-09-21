<?php

use App\Http\Controllers\AdminRequestController;
use App\Http\Controllers\AdminAccountController;
use App\Http\Controllers\AdminProfileController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceCheckInController;
use App\Http\Controllers\AttendanceManagementController;
use App\Http\Controllers\EmployeeProfileController;
use App\Http\Controllers\EmployeeRequestController;
use App\Http\Controllers\EmployeeTaskController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HrManagementController;
use App\Http\Controllers\HrAttendanceController;
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
        Route::get('/attendance/check-in', [AttendanceController::class, 'create'])
            ->name('attendance.check-in');
        Route::post('/attendance/check-in', [AttendanceController::class, 'store'])
            ->name('attendance.check-in.store');
        Route::get('/attendance/check-out', [AttendanceController::class, 'checkoutConfirmation'])
            ->name('attendance.check-out.confirmation');
        Route::post('/attendance/check-out', [AttendanceController::class, 'checkout'])
            ->name('attendance.check-out');
        Route::post('/attendance/check-out/{_compat?}', [AttendanceController::class, 'checkout'])
            ->name('attendance.checkout');
    });

    Route::prefix('admin')->middleware('role:admin')->group(function (): void {
        Route::get('/', [RoleHomeController::class, 'admin'])->name('admin.home');
        Route::get('/accounts', [AdminAccountController::class, 'index'])->name('admin.accounts');
        Route::post('/accounts', [AdminAccountController::class, 'store'])->name('admin.accounts.store');
        Route::patch('/accounts/{user}', [AdminAccountController::class, 'update'])->name('admin.accounts.update');
        Route::get('/profile', [AdminProfileController::class, 'show'])->name('admin.profile');
        Route::patch('/profile', [AdminProfileController::class, 'update'])->name('admin.profile.update');
        Route::get('/dashboard', [RoleHomeController::class, 'admin'])->name('admin.dashboard');
        Route::get('/requests', [AdminRequestController::class, 'index'])->name('admin.requests');
        Route::get('/requests/{requestModel}', [AdminRequestController::class, 'show'])->name('admin.requests.show');
        Route::put('/requests/{requestModel}', [AdminRequestController::class, 'process'])->name('admin.requests.process');
    });

    Route::prefix('hr')->middleware('role:hr')->group(function (): void {
        Route::get('/', [HrManagementController::class, 'dashboard'])->name('hr.home');
        Route::get('/dashboard', [HrManagementController::class, 'dashboard'])->name('hr.dashboard');
        Route::middleware('employee.checked.in')->group(function (): void {
        Route::get('/employees', [HrManagementController::class, 'employees'])->name('hr.employees');
        Route::get('/departments', [HrManagementController::class, 'departments'])->name('hr.departments');
        Route::get('/positions', [HrManagementController::class, 'positions'])->name('hr.positions');
        Route::get('/attendance', [AttendanceManagementController::class, 'index'])->name('hr.attendance.index');
        Route::get('/attendance/{attendance}', [AttendanceManagementController::class, 'show'])->name('hr.attendances.show');
        Route::match(['put', 'patch'], '/attendance/{attendance}', [AttendanceManagementController::class, 'update'])->name('hr.attendances.update');
        // Compatibility names used by legacy HR attendance views and tests.
        // The optional segment keeps the generated URL identical while avoiding
        // duplicate route names for the same URI in Laravel's route collection.
        Route::get('/attendance/{_compat?}', [AttendanceManagementController::class, 'index'])->name('hr.attendances.index');
        Route::get('/attendance/{attendance}/{_compat?}', [AttendanceManagementController::class, 'show'])->name('hr.attendance.show');
        Route::match(['put', 'patch'], '/attendance/{attendance}/{_compat?}', [AttendanceManagementController::class, 'update'])->name('hr.attendance.update');
        Route::get('/tasks', [HrManagementController::class, 'tasks'])->name('hr.tasks');
        Route::get('/requests', [HrManagementController::class, 'requests'])->name('hr.requests');
        Route::get('/recruitment', [HrManagementController::class, 'recruitment'])->name('hr.recruitment');
        Route::get('/termination', [HrManagementController::class, 'termination'])->name('hr.termination');
        Route::get('/profile', [HrManagementController::class, 'profile'])->name('hr.profile');
        });
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
