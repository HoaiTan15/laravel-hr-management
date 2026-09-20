<?php

use App\Http\Controllers\AdminRequestController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceCheckInController;
use App\Http\Controllers\AttendanceManagementController;
use App\Http\Controllers\EmployeeProfileController;
use App\Http\Controllers\EmployeeRequestController;
use App\Http\Controllers\EmployeeTaskController;
use App\Http\Controllers\HomeController;
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
        Route::view('/accounts', 'admin.accounts.index')->name('admin.accounts');
        Route::view('/profile', 'admin.profile.index')->name('admin.profile');
        Route::get('/dashboard', [RoleHomeController::class, 'admin'])->name('admin.dashboard');
        Route::get('/requests', [AdminRequestController::class, 'index'])->name('admin.requests');
        Route::get('/requests/{requestModel}', [AdminRequestController::class, 'show'])->name('admin.requests.show');
        Route::put('/requests/{requestModel}', [AdminRequestController::class, 'process'])->name('admin.requests.process');
    });

    Route::prefix('hr')->middleware(['role:hr', 'employee.checked.in'])->group(function (): void {
        Route::get('/', [RoleHomeController::class, 'hr'])->name('hr.home');
        Route::view('/dashboard', 'ui.module', ['role' => 'HR', 'title' => 'Dashboard HR', 'description' => 'Tổng quan nhân sự và hoạt động trong ngày.', 'sectionTitle' => 'Tổng quan HR'])->name('hr.dashboard');
        Route::view('/employees', 'ui.module', ['role' => 'HR', 'title' => 'Quản lý nhân viên', 'description' => 'Danh sách và hồ sơ nhân viên theo thiết kế HUIT HRMs.', 'sectionTitle' => 'Danh sách nhân viên'])->name('hr.employees');
        Route::view('/departments', 'ui.module', ['role' => 'HR', 'title' => 'Quản lý phòng ban', 'description' => 'Cơ cấu tổ chức các đơn vị trực thuộc.', 'sectionTitle' => 'Danh sách phòng ban'])->name('hr.departments');
        Route::view('/positions', 'ui.module', ['role' => 'HR', 'title' => 'Quản lý chức vụ', 'description' => 'Danh mục chức vụ trong hệ thống.', 'sectionTitle' => 'Danh sách chức vụ'])->name('hr.positions');
        Route::get('/attendance', [AttendanceManagementController::class, 'index'])->name('hr.attendance.index');
        Route::get('/attendance/{attendance}', [AttendanceManagementController::class, 'show'])->name('hr.attendances.show');
        Route::match(['put', 'patch'], '/attendance/{attendance}', [AttendanceManagementController::class, 'update'])->name('hr.attendances.update');
        Route::view('/tasks', 'ui.module', ['role' => 'HR', 'title' => 'Danh sách công việc', 'description' => 'Theo dõi công việc trong không gian HR.', 'sectionTitle' => 'Công việc'])->name('hr.tasks');
        Route::view('/requests', 'ui.module', ['role' => 'HR', 'title' => 'Phiếu yêu cầu', 'description' => 'Theo dõi phiếu yêu cầu của nhân sự.', 'sectionTitle' => 'Danh sách PYC'])->name('hr.requests');
        Route::view('/recruitment', 'ui.module', ['role' => 'HR', 'title' => 'Tuyển dụng', 'description' => 'Giao diện quy trình tuyển dụng theo Stitch.', 'sectionTitle' => 'Tuyển dụng'])->name('hr.recruitment');
        Route::view('/termination', 'ui.module', ['role' => 'HR', 'title' => 'Thôi việc', 'description' => 'Giao diện quy trình thôi việc theo Stitch.', 'sectionTitle' => 'Thôi việc'])->name('hr.termination');
        Route::view('/profile', 'ui.module', ['role' => 'HR', 'title' => 'Hồ sơ cá nhân', 'description' => 'Thông tin hồ sơ của tài khoản HR.', 'sectionTitle' => 'Hồ sơ HR'])->name('hr.profile');
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
