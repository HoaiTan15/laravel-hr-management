<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminAccountController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()->latest();

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(fn ($builder) => $builder
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"));
        }

        if ($request->filled('role') && UserRole::tryFrom($request->string('role')->toString())) {
            $query->where('role', $request->string('role')->toString());
        }

        if ($request->has('status') && in_array($request->string('status')->toString(), ['active', 'inactive'], true)) {
            $query->where('is_active', $request->string('status')->toString() === 'active');
        }

        return view('admin.accounts.index', [
            'role' => 'admin',
            'active' => 'accounts',
            'title' => 'Quản lý tài khoản',
            'topTitle' => 'Cổng quản trị',
            'topSub' => 'Quản lý tài khoản và phân quyền',
            'users' => $query->paginate(10)->withQueryString(),
            'roles' => UserRole::cases(),
            'totalUsers' => User::count(),
            'activeUsers' => User::where('is_active', true)->count(),
            'adminUsers' => User::whereIn('role', [UserRole::ADMIN, UserRole::HR])->count(),
            'inactiveUsers' => User::where('is_active', false)->count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::enum(UserRole::class)],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.accounts')->with('success', 'Đã tạo tài khoản mới.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::enum(UserRole::class)],
            'is_active' => ['required', 'boolean'],
        ]);

        if ($user->is($request->user()) && ! $request->boolean('is_active')) {
            return back()->withInput()->withErrors(['is_active' => 'Không thể khóa tài khoản Admin đang đăng nhập.']);
        }

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'is_active' => $request->boolean('is_active'),
        ] + (filled($data['password'] ?? null) ? ['password' => Hash::make($data['password'])] : []));

        return redirect()->route('admin.accounts')->with('success', 'Đã cập nhật tài khoản.');
    }
}
