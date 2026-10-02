<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_user_management(): void
    {
        $admin = User::factory()->create(['employee_code' => 'ADMIN0001', 'role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get('/settings/users')
            ->assertOk();
    }

    public function test_standard_user_cannot_access_user_management(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        $this->actingAs($user)
            ->get('/settings/users')
            ->assertForbidden();
    }

    public function test_admin_can_create_internal_and_external_users_with_roles(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)->post('/settings/users', [
            'user_type' => 'internal',
            'role' => User::ROLE_ADMIN,
            'first_name' => 'New',
            'last_name' => 'Admin',
            'email' => 'new-admin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect('/settings/users');

        $this->actingAs($admin)->post('/settings/users', [
            'user_type' => 'external',
            'role' => User::ROLE_USER,
            'first_name' => 'External',
            'last_name' => 'User',
            'email' => 'external@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect('/settings/users');

        $this->assertDatabaseHas('users', ['employee_code' => 'EMP0001', 'role' => User::ROLE_ADMIN]);
        $this->assertDatabaseHas('users', ['employee_code' => 'EXT0001', 'role' => User::ROLE_USER]);
    }

    public function test_user_creation_validates_required_fields_unique_email_and_password_confirmation(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN, 'email' => 'taken@example.com']);

        $this->actingAs($admin)->post('/settings/users', [
            'user_type' => 'invalid',
            'role' => 'owner',
            'first_name' => '',
            'last_name' => '',
            'email' => 'taken@example.com',
            'password' => 'short',
            'password_confirmation' => 'different',
        ])->assertSessionHasErrors(['user_type', 'role', 'first_name', 'last_name', 'email', 'password']);
    }

    public function test_admin_can_update_role_email_and_password(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create();

        $this->actingAs($admin)->put("/settings/users/{$user->id}", [
            'email' => 'changed@example.com',
            'role' => User::ROLE_ADMIN,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect('/settings/users');

        $user->refresh();
        $this->assertSame('changed@example.com', $user->email);
        $this->assertSame(User::ROLE_ADMIN, $user->role);
        $this->assertTrue(Hash::check('new-password', $user->password));
    }

    public function test_primary_admin_role_cannot_be_demoted_or_account_disabled(): void
    {
        $admin = User::factory()->create([
            'employee_code' => 'EMP0001',
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin)->put("/settings/users/{$admin->id}", [
            'email' => $admin->email,
            'role' => User::ROLE_USER,
            'password' => '',
            'password_confirmation' => '',
        ])->assertRedirect('/settings/users');

        $this->assertSame(User::ROLE_ADMIN, $admin->refresh()->role);

        $this->actingAs($admin)
            ->patch("/settings/users/{$admin->id}/active")
            ->assertStatus(422);
        $this->assertTrue($admin->refresh()->is_active);
    }

    public function test_admin_can_disable_and_enable_a_standard_user(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($admin)->patch("/settings/users/{$user->id}/active")->assertRedirect();
        $this->assertFalse($user->refresh()->is_active);

        $this->actingAs($admin)->patch("/settings/users/{$user->id}/active")->assertRedirect();
        $this->assertTrue($user->refresh()->is_active);
    }

    public function test_user_list_can_search_and_paginate(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
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

}
