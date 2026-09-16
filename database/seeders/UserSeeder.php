<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Demo Administrator', 'email' => 'admin@example.test', 'role' => UserRole::ADMIN],
            ['name' => 'Demo HR Manager', 'email' => 'hr@example.test', 'role' => UserRole::HR],
            ['name' => 'Demo Employee One', 'email' => 'employee@example.test', 'role' => UserRole::EMPLOYEE],
            ['name' => 'Demo Employee Two', 'email' => 'employee2@example.test', 'role' => UserRole::EMPLOYEE],
        ] as $account) {
            User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => Hash::make('demo-password'),
                    'role' => $account['role'],
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
            );
        }
    }
}
