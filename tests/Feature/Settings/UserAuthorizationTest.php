<?php

namespace Tests\Feature\Settings;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_user_management(): void
    {
        $admin = User::factory()->create(['employee_code' => 'ADMIN0001', 'role_id' => $this->roleId(Role::ADMIN_SLUG)]);

        $this->actingAs($admin)
            ->get('/settings/users')
            ->assertOk();
    }

    public function test_standard_user_is_redirected_from_user_management(): void
    {
        $user = User::factory()->create(['role_id' => $this->roleId(Role::USER_SLUG)]);

        $this->actingAs($user)
            ->get('/settings/users')
            ->assertRedirect('/customer-history')
            ->assertSessionHas('status', __('settings::messages.unauthorized'));

        $this->actingAs($user)
            ->get('/settings/users/999/edit')
            ->assertRedirect('/customer-history');
    }

    public function test_unauthorized_message_uses_the_selected_thai_locale(): void
    {
        $user = User::factory()->create(['role_id' => $this->roleId(Role::USER_SLUG)]);

        $this->withSession(['locale' => 'th'])
            ->actingAs($user)
            ->get('/settings/users')
            ->assertRedirect('/customer-history')
            ->assertSessionHas('status', 'คุณไม่มีสิทธิ์เข้าถึงหน้าจัดการผู้ใช้งาน');
    }

    public function test_admin_can_create_internal_and_external_users_with_roles(): void
    {
        $adminRoleId = $this->roleId(Role::ADMIN_SLUG);
        $userRoleId = $this->roleId(Role::USER_SLUG);
        $admin = User::factory()->create(['role_id' => $adminRoleId]);

        $this->actingAs($admin)->post('/settings/users', [
            'user_type' => 'internal',
            'role_id' => $adminRoleId,
            'first_name' => 'New',
            'last_name' => 'Admin',
            'email' => 'new-admin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect('/settings/users');

        $this->actingAs($admin)->post('/settings/users', [
            'user_type' => 'external',
            'role_id' => $userRoleId,
            'first_name' => 'External',
            'last_name' => 'User',
            'email' => 'external@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect('/settings/users');

        $this->assertDatabaseHas('users', ['employee_code' => 'EMP0001', 'role_id' => $adminRoleId]);
        $this->assertDatabaseHas('users', ['employee_code' => 'EXT0001', 'role_id' => $userRoleId]);
    }

    public function test_user_creation_validates_required_fields_unique_email_and_password_confirmation(): void
    {
        $admin = User::factory()->create(['role_id' => $this->roleId(Role::ADMIN_SLUG), 'email' => 'taken@example.com']);

        $this->actingAs($admin)->post('/settings/users', [
            'user_type' => 'invalid',
            'role_id' => 999999,
            'first_name' => '',
            'last_name' => '',
            'email' => 'taken@example.com',
            'password' => 'short',
            'password_confirmation' => 'different',
        ])->assertSessionHasErrors(['user_type', 'role_id', 'first_name', 'last_name', 'email', 'password']);
    }

    public function test_admin_can_update_role_email_and_password(): void
    {
        $adminRoleId = $this->roleId(Role::ADMIN_SLUG);
        $admin = User::factory()->create(['role_id' => $adminRoleId]);
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->get("/settings/users/{$user->id}/edit")
            ->assertOk();

        $this->actingAs($admin)->put("/settings/users/{$user->id}", [
            'email' => 'changed@example.com',
            'role_id' => $adminRoleId,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect('/settings/users');

        $user->refresh();
        $this->assertSame('changed@example.com', $user->email);
        $this->assertSame($adminRoleId, $user->role_id);
        $this->assertSame(Role::ADMIN_SLUG, $user->role->slug);
        $this->assertTrue(Hash::check('new-password', $user->password));
    }

    public function test_only_admin_and_user_roles_can_be_assigned(): void
    {
        $admin = User::factory()->create(['role_id' => $this->roleId(Role::ADMIN_SLUG)]);
        $managerRole = Role::query()->create([
            'name' => 'Manager',
            'slug' => 'manager',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get('/settings/users/create')
            ->assertOk()
            ->assertSee(__('settings::messages.role_admin'))
            ->assertSee(__('settings::messages.role_user'))
            ->assertDontSee('Manager');

        $this->actingAs($admin)->post('/settings/users', [
            'user_type' => 'internal',
            'role_id' => $managerRole->id,
            'first_name' => 'Loan',
            'last_name' => 'Manager',
            'email' => 'loan-manager@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHasErrors('role_id');

        $this->assertDatabaseMissing('users', ['email' => 'loan-manager@example.com']);
    }

    public function test_primary_admin_role_cannot_be_demoted_or_account_disabled(): void
    {
        $admin = User::factory()->create([
            'employee_code' => 'EMP0001',
            'role_id' => $this->roleId(Role::ADMIN_SLUG),
        ]);

        $this->actingAs($admin)->put("/settings/users/{$admin->id}", [
            'email' => $admin->email,
            'role_id' => $this->roleId(Role::USER_SLUG),
            'password' => '',
            'password_confirmation' => '',
        ])->assertRedirect('/settings/users');

        $this->assertTrue($admin->refresh()->isAdmin());

        $this->actingAs($admin)
            ->patch("/settings/users/{$admin->id}/active")
            ->assertStatus(422);
        $this->assertTrue($admin->refresh()->is_active);
    }

    public function test_admin_can_disable_and_enable_a_standard_user(): void
    {
        $admin = User::factory()->create(['role_id' => $this->roleId(Role::ADMIN_SLUG)]);
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($admin)->patch("/settings/users/{$user->id}/active")->assertRedirect();
        $this->assertFalse($user->refresh()->is_active);

        $this->actingAs($admin)->patch("/settings/users/{$user->id}/active")->assertRedirect();
        $this->assertTrue($user->refresh()->is_active);
    }

    public function test_user_list_can_search_and_paginate(): void
    {
        $admin = User::factory()->create(['role_id' => $this->roleId(Role::ADMIN_SLUG)]);
        User::factory()->create(['first_name' => 'Needle', 'email' => 'needle@example.com']);
        User::factory()->count(6)->create();

        $this->actingAs($admin)
            ->get('/settings/users?q=Needle&per_page=5')
            ->assertOk()
            ->assertSee('needle@example.com')
            ->assertViewHas('users', fn ($users) => $users->total() === 1
                && $users->first()->email === 'needle@example.com');

        $this->actingAs($admin)
            ->get('/settings/users?per_page=5&page=2')
            ->assertOk();
    }

    private function roleId(string $slug): int
    {
        return Role::query()->where('slug', $slug)->valueOrFail('id');
    }

}
