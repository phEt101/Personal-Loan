<?php

namespace Tests\Feature\Report;

use App\Modules\Settings\Models\ResponsibilityGroup;
use App\Modules\Settings\Models\Role;
use App\Modules\Settings\Models\User;
use App\Modules\WorkDelegation\Models\WorkDelegation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use ZipArchive;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_internal_user_can_view_both_reports(): void
    {
        $group = ResponsibilityGroup::query()->create(['name' => 'Report team', 'is_active' => true]);
        $internal = User::factory()->create([
            'user_type' => 'internal',
            'responsibility_group_id' => $group->id,
            'is_active' => true,
        ]);
        $external = User::factory()->create([
            'user_type' => 'external',
            'responsibility_group_id' => $group->id,
            'is_active' => true,
        ]);
        $this->insertCustomer('REPORT-00000001', $external, 'transferred', $internal);

        $this->actingAs($internal)
            ->get('/reports?report=transfers&submitted=1')
            ->assertOk()
            ->assertSee('REPORT-00000001');

        $this->get('/reports?report=customers&submitted=1')
            ->assertOk()
            ->assertSee($external->full_name)
            ->assertSee('REPORT-00000001')
            ->assertViewHas('customerRows', fn ($rows) => $rows->first()->CustomerNo === 'REPORT-00000001');
    }

    public function test_confirmation_records_the_delegation_used_for_access(): void
    {
        $group = ResponsibilityGroup::query()->create(['name' => 'Delegated team', 'is_active' => true]);
        $owner = User::factory()->create([
            'user_type' => 'external',
            'responsibility_group_id' => $group->id,
            'is_active' => true,
        ]);
        $delegator = User::factory()->create([
            'user_type' => 'internal',
            'responsibility_group_id' => $group->id,
            'is_active' => true,
        ]);
        $delegate = User::factory()->create([
            'user_type' => 'internal',
            'responsibility_group_id' => null,
            'is_active' => true,
        ]);
        $delegation = WorkDelegation::query()->create([
            'responsibility_group_id' => $group->id,
            'delegator_user_id' => $delegator->id,
            'delegate_user_id' => $delegate->id,
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addHour(),
            'created_by' => $delegator->id,
        ]);
        $this->insertCustomer('REPORT-00000002', $owner);

        $this->actingAs($delegate)
            ->patchJson('/customer-history/REPORT-00000002/hmeter-transfer')
            ->assertOk();

        $this->assertDatabaseHas('customers', [
            'CustomerNo' => 'REPORT-00000002',
            'HmeterTransferredBy' => $delegate->id,
            'HmeterWorkDelegationId' => $delegation->id,
        ]);

        $delegation->update(['ends_at' => now()->subMinute()]);

        $this->get('/reports?report=transfers&transfer_source=delegated&submitted=1')
            ->assertOk()
            ->assertSee('REPORT-00000002')
            ->assertSee(__('report::messages.delegated'));
    }

    public function test_external_user_cannot_access_reports(): void
    {
        $external = User::factory()->create(['user_type' => 'external', 'is_active' => true]);

        $this->actingAs($external)
            ->get('/reports')
            ->assertRedirect('/customer-history');
    }

    public function test_report_partial_request_returns_only_replaceable_content(): void
    {
        $internal = User::factory()->create([
            'user_type' => 'internal',
            'is_active' => true,
        ]);

        $this->actingAs($internal)
            ->get('/reports?report=customers&partial=1')
            ->assertOk()
            ->assertViewIs('report::_report_content')
            ->assertSee('report-filter-grid', false)
            ->assertDontSee('reportContentRegion', false)
            ->assertDontSee('<html', false);
    }

    public function test_report_does_not_load_rows_until_search_is_submitted(): void
    {
        $internal = User::factory()->create([
            'user_type' => 'internal',
            'is_active' => true,
        ]);

        $this->actingAs($internal)
            ->get('/reports?report=customers')
            ->assertOk()
            ->assertViewHas('hasSearched', false)
            ->assertViewHas('customerRows', null)
            ->assertSee(__('report::messages.search_prompt'));
    }

    public function test_external_filter_only_contains_standard_users_and_identifies_their_groups(): void
    {
        $group = ResponsibilityGroup::query()->create(['name' => 'Filtered team', 'is_active' => true]);
        $internal = User::factory()->create([
            'user_type' => 'internal',
            'responsibility_group_id' => $group->id,
            'is_active' => true,
        ]);
        $externalUser = User::factory()->create([
            'user_type' => 'external',
            'responsibility_group_id' => $group->id,
            'first_name' => 'External',
            'last_name' => 'User',
            'is_active' => true,
        ]);
        $externalManager = User::factory()->create([
            'user_type' => 'external',
            'role_id' => Role::query()->where('slug', Role::MANAGER_SLUG)->valueOrFail('id'),
            'first_name' => 'External',
            'last_name' => 'Manager',
            'is_active' => true,
        ]);

        $this->actingAs($internal)
            ->get('/reports?report=customers')
            ->assertOk()
            ->assertSee('data-group-id="'.$group->id.'"', false)
            ->assertSee($externalUser->full_name)
            ->assertDontSee($externalManager->full_name);
    }

    public function test_standard_internal_filter_options_are_limited_to_owned_and_actively_delegated_groups(): void
    {
        $ownGroup = ResponsibilityGroup::query()->create(['name' => 'Own report group', 'is_active' => true]);
        $otherGroup = ResponsibilityGroup::query()->create(['name' => 'Other report group', 'is_active' => true]);
        $internal = User::factory()->create([
            'user_type' => 'internal',
            'responsibility_group_id' => $ownGroup->id,
            'is_active' => true,
        ]);
        $delegator = User::factory()->create([
            'user_type' => 'internal',
            'responsibility_group_id' => $otherGroup->id,
            'is_active' => true,
        ]);
        $ownExternal = User::factory()->create([
            'user_type' => 'external',
            'responsibility_group_id' => $ownGroup->id,
            'first_name' => 'Own',
            'last_name' => 'External',
            'is_active' => true,
        ]);
        $otherExternal = User::factory()->create([
            'user_type' => 'external',
            'responsibility_group_id' => $otherGroup->id,
            'first_name' => 'Other',
            'last_name' => 'External',
            'is_active' => true,
        ]);

        $this->actingAs($internal)
            ->get('/reports?report=customers')
            ->assertOk()
            ->assertSee($ownGroup->name)
            ->assertSee($ownExternal->full_name)
            ->assertDontSee($otherGroup->name)
            ->assertDontSee($otherExternal->full_name);

        WorkDelegation::query()->create([
            'responsibility_group_id' => $otherGroup->id,
            'delegator_user_id' => $delegator->id,
            'delegate_user_id' => $internal->id,
            'starts_at' => now()->subMinute(),
            'ends_at' => now()->addHour(),
            'created_by' => $delegator->id,
        ]);

        $this->get('/reports?report=customers')
            ->assertOk()
            ->assertSee($otherGroup->name)
            ->assertSee($otherExternal->full_name);
    }

    public function test_customer_report_can_filter_by_transfer_status(): void
    {
        $group = ResponsibilityGroup::query()->create(['name' => 'Status team', 'is_active' => true]);
        $internal = User::factory()->create([
            'user_type' => 'internal',
            'responsibility_group_id' => $group->id,
            'is_active' => true,
        ]);
        $external = User::factory()->create([
            'user_type' => 'external',
            'responsibility_group_id' => $group->id,
            'is_active' => true,
        ]);
        $this->insertCustomer('STATUS-PENDING', $external);
        $this->insertCustomer('STATUS-CONFIRMED', $external, 'transferred', $internal);

        $this->actingAs($internal)
            ->get('/reports?report=customers&transfer_status=pending&submitted=1')
            ->assertOk()
            ->assertSee('STATUS-PENDING')
            ->assertDontSee('STATUS-CONFIRMED');
    }

    public function test_searched_report_is_paginated_and_keeps_search_state(): void
    {
        $group = ResponsibilityGroup::query()->create(['name' => 'Pagination team', 'is_active' => true]);
        $internal = User::factory()->create([
            'user_type' => 'internal',
            'responsibility_group_id' => $group->id,
            'is_active' => true,
        ]);
        $external = User::factory()->create([
            'user_type' => 'external',
            'responsibility_group_id' => $group->id,
            'is_active' => true,
        ]);

        foreach (range(1, 21) as $index) {
            $this->insertCustomer('PAGE-'.str_pad((string) $index, 4, '0', STR_PAD_LEFT), $external);
        }

        $this->actingAs($internal)
            ->get('/reports?report=customers&submitted=1')
            ->assertOk()
            ->assertViewHas('customerRows', fn ($rows) => $rows->total() === 21
                && $rows->perPage() === 10
                && $rows->lastPage() === 3
                && str_contains((string) $rows->nextPageUrl(), 'submitted=1'));
    }

    public function test_internal_user_can_download_filtered_customer_report_as_xlsx(): void
    {
        $group = ResponsibilityGroup::query()->create(['name' => 'Excel team', 'is_active' => true]);
        $internal = User::factory()->create([
            'user_type' => 'internal',
            'responsibility_group_id' => $group->id,
            'is_active' => true,
        ]);
        $external = User::factory()->create([
            'user_type' => 'external',
            'responsibility_group_id' => $group->id,
            'is_active' => true,
        ]);
        $this->insertCustomer('EXCEL-000000001', $external);

        $response = $this->actingAs($internal)
            ->get('/reports/export?report=customers&external_user_id='.$external->id)
            ->assertOk()
            ->assertDownload();

        $path = $response->baseResponse->getFile()->getPathname();
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path) === true);
        $sheet = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        $this->assertIsString($sheet);
        $this->assertStringContainsString('EXCEL-000000001', $sheet);
    }

    private function insertCustomer(
        string $customerNo,
        User $owner,
        string $status = 'pending',
        ?User $confirmer = null
    ): void {
        DB::table('customers')->insert([
            'CustomerNo' => $customerNo,
            'CustomerRefNo' => $customerNo,
            'Firstname' => 'Report',
            'Lastname' => 'Customer',
            'Email' => 'report@example.com',
            'sysInsertUserId' => $owner->id,
            'sysInsertDateTime' => now(),
            'HmeterTransferStatus' => $status,
            'HmeterTransferredBy' => $confirmer?->id,
            'HmeterTransferredAt' => $confirmer ? now() : null,
        ]);
    }
}
