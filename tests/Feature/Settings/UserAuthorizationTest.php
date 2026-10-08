<?php

namespace Tests\Feature\Settings;

use App\Modules\Settings\Models\Role;
use App\Modules\Settings\Models\User;
use Database\Seeders\CustomerMockSeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_user_management(): void
    {
        $admin = User::factory()->create([
            'employee_code' => 'ADMIN0001',
            'role_id' => $this->roleId(Role::ADMIN_SLUG),
            'first_name' => 'ธิดารัตน์',
            'last_name' => 'มูลเทพ',
            'created_at' => '2026-10-06 04:39:00',
        ]);

        $this->actingAs($admin)
            ->get('/settings/users')
            ->assertOk()
            ->assertSee('user-avatar-compact">ธ', false)
            ->assertSee('user-avatar-large">ธ', false)
            ->assertSee('06/10/2026 11:39');
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

    public function test_database_seeder_creates_three_external_managers(): void
    {
        $this->seed(DatabaseSeeder::class);
        $managerRoleId = $this->roleId(Role::MANAGER_SLUG);

        foreach ([
            ['EXT0001', 'phubeth.jul@wipay.co.th', 'ภูเบศ', 'จุลบล'],
            ['EXT0002', 'wanvisa.rue@wipay.co.th', 'วันวิสาข์', 'เรืองฉิม'],
            ['EXT0003', 'Yanothai.hem@wipay.co.th', 'ญาโณทัย', 'เหมวัฒน์'],
        ] as [$employeeCode, $email, $firstName, $lastName]) {
            $this->assertDatabaseHas('users', [
                'employee_code' => $employeeCode,
                'email' => $email,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'user_type' => 'external',
                'role_id' => $managerRoleId,
            ]);
            $this->assertTrue(Hash::check('P@ssw0rd', User::query()->where('employee_code', $employeeCode)->value('password')));
        }

        $this->assertSame(8, User::query()
            ->where('user_type', 'external')
            ->where('role_id', $this->roleId(Role::USER_SLUG))
            ->whereBetween('employee_code', ['EXT0004', 'EXT0011'])
            ->count());
        $this->assertLessThan(
            User::query()->where('employee_code', 'EXT0004')->valueOrFail('id'),
            User::query()->where('employee_code', 'EXT0003')->valueOrFail('id')
        );
        $this->assertTrue(User::query()
            ->where('user_type', 'external')
            ->where('role_id', $this->roleId(Role::USER_SLUG))
            ->whereBetween('employee_code', ['EXT0004', 'EXT0011'])
            ->get()
            ->every(fn (User $user) => Hash::check('P@ssw0rd', $user->password)));

        foreach ([
            ['EXT0004', 'ธีรศาสนติ์', 'เมธาปัฐวีร์', 'Theerasarn@wipay.co.th'],
            ['EXT0005', 'ฐิติมา', 'แซ่ลิ้ม', 'Thitima@wipay.co.th'],
            ['EXT0006', 'นาซือเร๊าะ', 'สาและ', 'Naserah@wipay.co.th'],
            ['EXT0007', 'วทัญญู', 'กลับสังข์', 'Wathanyu@wipay.co.th'],
        ] as [$employeeCode, $firstName, $lastName, $email]) {
            $this->assertDatabaseHas('users', [
                'employee_code' => $employeeCode,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'user_type' => 'external',
                'role_id' => $this->roleId(Role::USER_SLUG),
            ]);
        }

        $this->seed(CustomerMockSeeder::class);
        $this->assertSame(17, DB::table('customers')->count());
        $this->assertSame(0, DB::table('customers as customer')
            ->join('users as creator', 'creator.id', '=', 'customer.sysInsertUserId')
            ->join('roles as role', 'role.id', '=', 'creator.role_id')
            ->where('creator.user_type', 'external')
            ->where('role.slug', Role::MANAGER_SLUG)
            ->count());
        $this->assertSame(0, DB::table('customers as customer')
            ->join('users as creator', 'creator.id', '=', 'customer.sysInsertUserId')
            ->where('creator.user_type', 'internal')
            ->count());
        $this->assertSame(0, DB::table('customers as customer')
            ->join('users as creator', 'creator.id', '=', 'customer.sysInsertUserId')
            ->whereNotIn('creator.employee_code', ['EXT0004', 'EXT0005', 'EXT0006', 'EXT0007'])
            ->count());
        $this->assertSame(0, DB::table('customers as customer')
            ->join('users as creator', 'creator.id', '=', 'customer.sysInsertUserId')
            ->whereIn('creator.employee_code', ['EXT0008', 'EXT0009', 'EXT0010', 'EXT0011'])
            ->count());
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

    public function test_admin_manager_and_user_roles_can_be_assigned(): void
    {
        $admin = User::factory()->create(['role_id' => $this->roleId(Role::ADMIN_SLUG)]);
        $managerRoleId = $this->roleId(Role::MANAGER_SLUG);

        $this->actingAs($admin)
            ->get('/settings/users/create')
            ->assertOk()
            ->assertSee(__('settings::messages.role_admin'))
            ->assertSee(__('settings::messages.role_manager'))
            ->assertSee(__('settings::messages.role_user'))
            ->assertDontSee('Viewer');

        $this->actingAs($admin)->post('/settings/users', [
            'user_type' => 'external',
            'role_id' => $managerRoleId,
            'first_name' => 'Loan',
            'last_name' => 'Manager',
            'email' => 'loan-manager@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect('/settings/users');

        $this->assertDatabaseHas('users', [
            'email' => 'loan-manager@example.com',
            'user_type' => 'external',
            'role_id' => $managerRoleId,
        ]);
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

    public function test_user_list_can_sort_columns_in_both_directions(): void
    {
        $admin = User::factory()->create([
            'employee_code' => 'EMP0001',
            'role_id' => $this->roleId(Role::ADMIN_SLUG),
        ]);
        User::factory()->create(['employee_code' => 'EXT0002']);
        User::factory()->create(['employee_code' => 'EXT0001']);

        $this->actingAs($admin)
            ->get('/settings/users')
            ->assertOk()
            ->assertSee('aria-sort="ascending"', false)
            ->assertViewHas('users', fn ($users) => $users->pluck('employee_code')->all() === [
                'EMP0001', 'EXT0001', 'EXT0002',
            ]);

        $this->get('/settings/users?sort=employee_code&direction=desc')
            ->assertOk()
            ->assertSee('aria-sort="descending"', false)
            ->assertViewHas('users', fn ($users) => $users->pluck('employee_code')->all() === [
                'EXT0002', 'EXT0001', 'EMP0001',
            ]);
    }

    private function roleId(string $slug): int
    {
        return Role::query()->where('slug', $slug)->valueOrFail('id');
    }
}
