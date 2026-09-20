<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_real_accounts(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $user = User::factory()->create(['email' => 'real-user@example.test']);

        $this->actingAs($admin)
            ->get(route('admin.accounts'))
            ->assertOk()
            ->assertSee($user->email);
    }

    public function test_admin_can_create_an_account(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);

        $this->actingAs($admin)
            ->post(route('admin.accounts.store'), [
                'name' => 'New HR',
                'email' => 'new-hr@example.test',
                'role' => UserRole::HR->value,
                'password' => 'secret-password',
                'password_confirmation' => 'secret-password',
            ])
            ->assertRedirect(route('admin.accounts'));

        $created = User::where('email', 'new-hr@example.test')->firstOrFail();
        $this->assertSame(UserRole::HR, $created->role);
        $this->assertTrue(Hash::check('secret-password', $created->password));
    }

    public function test_admin_can_update_an_account(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $user = User::factory()->create(['role' => UserRole::EMPLOYEE]);

        $this->actingAs($admin)
            ->patch(route('admin.accounts.update', $user), [
                'name' => 'Updated User',
                'email' => $user->email,
                'role' => UserRole::HR->value,
                'is_active' => '0',
                'password' => '',
                'password_confirmation' => '',
            ])
            ->assertRedirect(route('admin.accounts'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated User',
            'role' => UserRole::HR->value,
            'is_active' => false,
        ]);
    }

    public function test_admin_cannot_disable_the_current_account(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);

        $this->actingAs($admin)
            ->patch(route('admin.accounts.update', $admin), [
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => UserRole::ADMIN->value,
                'is_active' => '0',
                'password' => '',
                'password_confirmation' => '',
            ])
            ->assertSessionHasErrors('is_active');

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'is_active' => true]);
    }

    public function test_admin_can_update_their_profile(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);

        $this->actingAs($admin)
            ->patch(route('admin.profile.update'), [
                'name' => 'Updated Administrator',
                'email' => 'updated-admin@example.test',
                'password' => 'new-secret-password',
                'password_confirmation' => 'new-secret-password',
            ])
            ->assertRedirect(route('admin.profile'));

        $admin->refresh();
        $this->assertSame('Updated Administrator', $admin->name);
        $this->assertSame('updated-admin@example.test', $admin->email);
        $this->assertTrue(Hash::check('new-secret-password', $admin->password));
    }

    public function test_non_admin_cannot_manage_admin_accounts(): void
    {
        $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);

        $this->actingAs($employee)
            ->get(route('admin.accounts'))
            ->assertForbidden();
    }
}
