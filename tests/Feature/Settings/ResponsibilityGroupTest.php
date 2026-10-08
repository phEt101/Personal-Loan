<?php

namespace Tests\Feature\Settings;

use App\Modules\Settings\Models\Role;
use App\Modules\Settings\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\ResponsibilityGroupSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ResponsibilityGroupTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_default_responsibility_groups(): void
    {
        $this->seed(DatabaseSeeder::class);

        foreach ([
            'A' => ['internal' => ['EMP0002'], 'external' => ['EXT0004', 'EXT0005']],
            'B' => ['internal' => ['EMP0003'], 'external' => ['EXT0006', 'EXT0007']],
        ] as $groupName => $members) {
            $groupId = DB::table('responsibility_groups')->where('name', $groupName)->value('id');
            $this->assertNotNull($groupId);
            $this->assertSame(
                $members['internal'],
                DB::table('users')
                    ->where('responsibility_group_id', $groupId)
                    ->where('user_type', 'internal')
                    ->orderBy('employee_code')
                    ->pluck('employee_code')
                    ->all()
            );
            $this->assertSame(
                $members['external'],
                DB::table('users')
                    ->where('responsibility_group_id', $groupId)
                    ->where('user_type', 'external')
                    ->orderBy('employee_code')
                    ->pluck('employee_code')
                    ->all()
            );
        }

        $this->seed(ResponsibilityGroupSeeder::class);
        $this->assertSame(2, DB::table('responsibility_groups')->whereIn('name', ['A', 'B'])->count());
    }

    public function test_admin_can_create_and_update_responsibility_group_members(): void
    {
        $admin = User::factory()->create(['role_id' => $this->roleId(Role::ADMIN_SLUG)]);
        $internal = User::factory()->create(['user_type' => 'internal']);
        $external = User::factory()->create(['user_type' => 'external']);

        $this->actingAs($admin)->post('/settings/responsibility-groups', [
            'name' => 'Team A',
            'is_active' => 1,
            'internal_user_ids' => [$internal->id],
            'external_user_ids' => [$external->id],
        ])->assertRedirect('/settings/responsibility-groups');

        $groupId = DB::table('responsibility_groups')->where('name', 'Team A')->value('id');
        $this->assertDatabaseHas('users', [
            'id' => $internal->id,
            'responsibility_group_id' => $groupId,
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $external->id,
            'responsibility_group_id' => $groupId,
        ]);

        $this->get('/settings/responsibility-groups')
            ->assertOk()
            ->assertSee('Team A')
            ->assertSee($internal->full_name)
            ->assertSee($external->full_name);

        $this->patch("/settings/users/{$external->id}/active")->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $external->id,
            'responsibility_group_id' => $groupId,
        ]);
        $this->assertFalse($external->fresh()->is_active);
    }

    public function test_moving_external_user_changes_its_group_without_changing_customer_data(): void
    {
        $admin = User::factory()->create(['role_id' => $this->roleId(Role::ADMIN_SLUG)]);
        $external = User::factory()->create(['user_type' => 'external']);
        $firstGroup = DB::table('responsibility_groups')->insertGetId([
            'name' => 'Team A', 'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $secondGroup = DB::table('responsibility_groups')->insertGetId([
            'name' => 'Team B', 'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $external->update(['responsibility_group_id' => $firstGroup]);
        DB::table('customers')->insert([
            'CustomerNo' => 'MOVE-GROUP-001',
            'Firstname' => 'Existing',
            'Lastname' => 'Customer',
            'Email' => 'existing.customer@example.com',
            'sysInsertUserId' => $external->id,
        ]);

        $this->actingAs($admin)->put("/settings/responsibility-groups/{$secondGroup}", [
            'name' => 'Team B',
            'is_active' => 1,
            'external_user_ids' => [$external->id],
        ])->assertRedirect('/settings/responsibility-groups');

        $this->assertDatabaseHas('users', [
            'id' => $external->id,
            'responsibility_group_id' => $secondGroup,
        ]);
        $this->assertDatabaseHas('customers', [
            'CustomerNo' => 'MOVE-GROUP-001',
            'sysInsertUserId' => $external->id,
        ]);
    }

    public function test_non_admin_cannot_manage_responsibility_groups(): void
    {
        $user = User::factory()->create(['role_id' => $this->roleId(Role::USER_SLUG)]);

        $this->actingAs($user)
            ->get('/settings/responsibility-groups')
            ->assertRedirect('/customer-history');
    }

    public function test_external_managers_are_not_available_or_assignable_to_groups(): void
    {
        $admin = User::factory()->create(['role_id' => $this->roleId(Role::ADMIN_SLUG)]);
        $manager = User::factory()->create([
            'employee_code' => 'EXT-MANAGER',
            'user_type' => 'external',
            'role_id' => $this->roleId(Role::MANAGER_SLUG),
        ]);
        $externalUser = User::factory()->create([
            'employee_code' => 'EXT-USER',
            'user_type' => 'external',
            'role_id' => $this->roleId(Role::USER_SLUG),
        ]);

        $this->actingAs($admin)
            ->get('/settings/responsibility-groups/create')
            ->assertOk()
            ->assertDontSee('EXT-MANAGER')
            ->assertSee('EXT-USER');

        $this->post('/settings/responsibility-groups', [
            'name' => 'Invalid Team',
            'is_active' => 1,
            'external_user_ids' => [$manager->id],
        ])->assertSessionHasErrors('external_user_ids.0');

        $this->assertDatabaseHas('users', [
            'id' => $manager->id,
            'responsibility_group_id' => null,
        ]);
    }

    private function roleId(string $slug): int
    {
        return (int) Role::query()->where('slug', $slug)->valueOrFail('id');
    }
}
