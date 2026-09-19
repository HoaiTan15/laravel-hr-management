<?php

use App\Http\Controllers\AttendanceController;
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
    Route::get('/attendance/check-in', [AttendanceController::class, 'create'])
        ->middleware('role:hr,employee')
        ->name('attendance.check-in');
    Route::post('/attendance/check-in', [AttendanceController::class, 'store'])
        ->middleware('role:hr,employee')
        ->name('attendance.check-in.store');
    Route::get('/attendance/check-out', [AttendanceController::class, 'checkoutConfirmation'])
        ->middleware('role:hr,employee')
        ->name('attendance.checkout.confirmation');
    Route::post('/attendance/check-out', [AttendanceController::class, 'checkout'])
        ->middleware('role:hr,employee')
        ->name('attendance.checkout');

    Route::prefix('hr/attendances')->middleware('role:hr')->group(function (): void {
        Route::get('/', [HrAttendanceController::class, 'index'])->name('hr.attendances.index');
        Route::get('/{attendance}', [HrAttendanceController::class, 'show'])->name('hr.attendances.show');
        Route::patch('/{attendance}', [HrAttendanceController::class, 'update'])->name('hr.attendances.update');
    });

    Route::prefix('admin')->middleware('role:admin')->group(function (): void {
        Route::get('/', [RoleHomeController::class, 'admin'])->name('admin.home');
        Route::view('/dashboard', 'ui.module', ['role' => 'Admin', 'title' => 'Dashboard Admin', 'description' => 'Tổng quan tài khoản và phiếu yêu cầu.', 'sectionTitle' => 'Tổng quan Admin'])->name('admin.dashboard');
        Route::view('/requests', 'ui.module', ['role' => 'Admin', 'title' => 'Danh sách PYC', 'description' => 'Theo dõi và xử lý phiếu yêu cầu trong không gian Admin.', 'sectionTitle' => 'Phiếu yêu cầu'])->name('admin.requests');
        Route::view('/accounts', 'ui.module', ['role' => 'Admin', 'title' => 'Quản lý tài khoản', 'description' => 'Giao diện quản lý tài khoản theo thiết kế HUIT HRMs.', 'sectionTitle' => 'Tài khoản người dùng'])->name('admin.accounts');
        Route::view('/profile', 'ui.module', ['role' => 'Admin', 'title' => 'Hồ sơ cá nhân', 'description' => 'Thông tin hồ sơ của tài khoản Admin.', 'sectionTitle' => 'Hồ sơ Admin'])->name('admin.profile');
    });

    Route::prefix('hr')->middleware(['role:hr', 'employee.checked.in'])->group(function (): void {
        Route::get('/', [RoleHomeController::class, 'hr'])->name('hr.home');
        Route::view('/dashboard', 'ui.module', ['role' => 'HR', 'title' => 'Dashboard HR', 'description' => 'Tổng quan nhân sự và hoạt động trong ngày.', 'sectionTitle' => 'Tổng quan HR'])->name('hr.dashboard');
        Route::view('/employees', 'ui.module', ['role' => 'HR', 'title' => 'Quản lý nhân viên', 'description' => 'Danh sách và hồ sơ nhân viên theo thiết kế HUIT HRMs.', 'sectionTitle' => 'Danh sách nhân viên'])->name('hr.employees');
        Route::view('/departments', 'ui.module', ['role' => 'HR', 'title' => 'Quản lý phòng ban', 'description' => 'Cơ cấu tổ chức các đơn vị trực thuộc.', 'sectionTitle' => 'Danh sách phòng ban'])->name('hr.departments');
        Route::view('/positions', 'ui.module', ['role' => 'HR', 'title' => 'Quản lý chức vụ', 'description' => 'Danh mục chức vụ trong hệ thống.', 'sectionTitle' => 'Danh sách chức vụ'])->name('hr.positions');
        Route::view('/attendance', 'ui.module', ['role' => 'HR', 'title' => 'Quản lý chấm công', 'description' => 'Giao diện quản lý chấm công theo thiết kế HUIT HRMs.', 'sectionTitle' => 'Chấm công'])->name('hr.attendance');
        Route::view('/tasks', 'ui.module', ['role' => 'HR', 'title' => 'Danh sách công việc', 'description' => 'Theo dõi công việc trong không gian HR.', 'sectionTitle' => 'Công việc'])->name('hr.tasks');
        Route::view('/requests', 'ui.module', ['role' => 'HR', 'title' => 'Phiếu yêu cầu', 'description' => 'Theo dõi phiếu yêu cầu của nhân sự.', 'sectionTitle' => 'Danh sách PYC'])->name('hr.requests');
        Route::view('/recruitment', 'ui.module', ['role' => 'HR', 'title' => 'Tuyển dụng', 'description' => 'Giao diện quy trình tuyển dụng theo Stitch.', 'sectionTitle' => 'Tuyển dụng'])->name('hr.recruitment');
        Route::view('/termination', 'ui.module', ['role' => 'HR', 'title' => 'Thôi việc', 'description' => 'Giao diện quy trình thôi việc theo Stitch.', 'sectionTitle' => 'Thôi việc'])->name('hr.termination');
        Route::view('/profile', 'ui.module', ['role' => 'HR', 'title' => 'Hồ sơ cá nhân', 'description' => 'Thông tin hồ sơ của tài khoản HR.', 'sectionTitle' => 'Hồ sơ HR'])->name('hr.profile');
    });

    Route::prefix('employee')->middleware(['role:employee', 'employee.checked.in'])->group(function (): void {
        Route::get('/', [RoleHomeController::class, 'employee'])->name('employee.home');
    });
});
