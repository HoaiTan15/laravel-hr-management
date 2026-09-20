<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminProfileController extends Controller
{
    public function show(Request $request): View
    {
        return view('admin.profile.index', [
            'role' => 'admin',
            'active' => 'profile',
            'title' => 'Hồ sơ cá nhân',
            'topTitle' => 'Cổng quản trị',
            'topSub' => 'Hồ sơ quản trị viên',
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ] + (filled($data['password'] ?? null) ? ['password' => Hash::make($data['password'])] : []));

        return redirect()->route('admin.profile')->with('success', 'Đã cập nhật hồ sơ Admin.');
    }
}
