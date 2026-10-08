<?php

namespace Tests\Feature\CustomerHistory;

use App\Models\ResponsibilityGroup;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkDelegation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WorkDelegationTest extends TestCase
{
    use RefreshDatabase;

    public function test_internal_user_can_delegate_own_group_and_recipient_sees_group_customers(): void
    {
        $group = ResponsibilityGroup::query()->create(['name' => 'Team A', 'is_active' => true]);
        $delegator = $this->internalUser($group->id);
        $delegate = $this->internalUser();
        $external = User::factory()->create([
            'user_type' => 'external',
            'role_id' => $this->roleId(Role::USER_SLUG),
            'responsibility_group_id' => $group->id,
        ]);
        $this->insertCustomer('DELEGATED-001', $external);

        $this->actingAs($delegate)
            ->get('/customer-history')
            ->assertOk()
            ->assertViewHas('customers', fn ($customers) => $customers->total() === 0);

        $this->actingAs($delegator)->post('/work-delegations', [
            'responsibility_group_id' => $group->id,
            'delegate_user_id' => $delegate->id,
            'starts_at' => now()->subHour()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'note' => 'Annual leave',
        ])->assertRedirect('/work-delegations');

        $this->actingAs($delegate)
            ->get('/customer-history')
            ->assertOk()
            ->assertViewHas('customers', fn ($customers) => $customers->pluck('CustomerNo')->all() === ['DELEGATED-001']
                && $customers->first()->WorkSourceType === 'delegated'
                && $customers->first()->DelegatedByName === $delegator->full_name);

        $delegation = WorkDelegation::query()->firstOrFail();
        $this->actingAs($delegator)
            ->patch("/work-delegations/{$delegation->id}/cancel")
            ->assertRedirect('/work-delegations');

        $this->actingAs($delegate)
            ->get('/customer-history')
            ->assertViewHas('customers', fn ($customers) => $customers->total() === 0);
    }

    public function test_future_ended_and_cancelled_delegations_do_not_grant_customer_access(): void
    {
        $group = ResponsibilityGroup::query()->create(['name' => 'Team A', 'is_active' => true]);
        $delegator = $this->internalUser($group->id);
        $delegate = $this->internalUser();
        $external = User::factory()->create([
            'user_type' => 'external',
            'role_id' => $this->roleId(Role::USER_SLUG),
            'responsibility_group_id' => $group->id,
        ]);
        $this->insertCustomer('DELEGATED-002', $external);

        foreach ([
            [now()->addDay(), now()->addDays(2), null],
            [now()->subDays(2), now()->subDay(), null],
            [now()->subHour(), now()->addDay(), now()],
        ] as [$startsAt, $endsAt, $cancelledAt]) {
            WorkDelegation::query()->create([
                'responsibility_group_id' => $group->id,
                'delegator_user_id' => $delegator->id,
                'delegate_user_id' => $delegate->id,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'created_by' => $delegator->id,
                'cancelled_at' => $cancelledAt,
            ]);
        }

        $this->actingAs($delegate)
            ->get('/customer-history')
            ->assertOk()
            ->assertViewHas('customers', fn ($customers) => $customers->total() === 0);
    }

    public function test_external_user_cannot_access_work_delegations(): void
    {
        $external = User::factory()->create([
            'user_type' => 'external',
            'role_id' => $this->roleId(Role::USER_SLUG),
        ]);

        $this->actingAs($external)
            ->get('/work-delegations')
            ->assertRedirect('/customer-history');
    }

    public function test_admin_cannot_be_selected_or_submitted_as_delegate(): void
    {
        $group = ResponsibilityGroup::query()->create(['name' => 'Team A', 'is_active' => true]);
        $delegator = $this->internalUser($group->id);
        $admin = User::factory()->create([
            'employee_code' => 'ADMIN-DELEGATE',
            'user_type' => 'internal',
            'role_id' => $this->roleId(Role::ADMIN_SLUG),
        ]);

        $this->actingAs($delegator)
            ->get('/work-delegations/create')
            ->assertOk()
            ->assertDontSee('ADMIN-DELEGATE');

        $this->post('/work-delegations', [
            'responsibility_group_id' => $group->id,
            'delegate_user_id' => $admin->id,
            'starts_at' => now()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addDay()->format('Y-m-d H:i:s'),
        ])->assertSessionHasErrors('delegate_user_id');

        $this->assertDatabaseCount('work_delegations', 0);
    }

    public function test_internal_user_cannot_delegate_another_group_or_manage_another_users_delegation(): void
    {
        $ownGroup = ResponsibilityGroup::query()->create(['name' => 'Team A', 'is_active' => true]);
        $otherGroup = ResponsibilityGroup::query()->create(['name' => 'Team B', 'is_active' => true]);
        $delegator = $this->internalUser($ownGroup->id);
        $otherDelegator = $this->internalUser($otherGroup->id);
        $delegate = $this->internalUser();

        $this->actingAs($delegator)->post('/work-delegations', [
            'responsibility_group_id' => $otherGroup->id,
            'delegate_user_id' => $delegate->id,
            'starts_at' => now()->addHour()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addDay()->format('Y-m-d H:i:s'),
        ])->assertSessionHasErrors('responsibility_group_id');

        $delegation = WorkDelegation::query()->create([
            'responsibility_group_id' => $otherGroup->id,
            'delegator_user_id' => $otherDelegator->id,
            'delegate_user_id' => $delegate->id,
            'starts_at' => now()->addHour(),
            'ends_at' => now()->addDay(),
            'created_by' => $otherDelegator->id,
        ]);

        $this->get("/work-delegations/{$delegation->id}/edit")->assertForbidden();
        $this->patch("/work-delegations/{$delegation->id}/cancel")->assertForbidden();
    }

    private function internalUser(?int $groupId = null): User
    {
        return User::factory()->create([
            'user_type' => 'internal',
            'role_id' => $this->roleId(Role::USER_SLUG),
            'responsibility_group_id' => $groupId,
        ]);
    }

    private function roleId(string $slug): int
    {
        return (int) Role::query()->where('slug', $slug)->valueOrFail('id');
    }

    private function insertCustomer(string $customerNo, User $creator): void
    {
        DB::table('customers')->insert([
            'CustomerNo' => $customerNo,
            'Firstname' => 'Delegated',
            'Lastname' => 'Customer',
            'Email' => strtolower($customerNo).'@example.com',
            'sysInsertUserId' => $creator->id,
            'sysInsertDateTime' => now(),
        ]);
    }
}
