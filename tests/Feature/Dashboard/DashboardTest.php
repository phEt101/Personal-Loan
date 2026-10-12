<?php

namespace Tests\Feature\Dashboard;

use App\Modules\Settings\Models\ResponsibilityGroup;
use App\Modules\Settings\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private const DASHBOARD_PATH = '/dashboard';

    private const CUSTOMER_HISTORY_PATH = '/customer-history';

    public function test_authenticated_user_can_view_dashboard_without_recent_activity(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user)
            ->get(self::DASHBOARD_PATH)
            ->assertOk()
            ->assertSee(__('dashboard::messages.title'))
            ->assertSee(__('dashboard::messages.work_queue'))
            ->assertSee(__('dashboard::messages.group_performance'))
            ->assertDontSee('กิจกรรมล่าสุด');
    }

    public function test_authenticated_root_and_login_redirect_to_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'dashboard@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);

        $this->actingAs($user)->get('/')->assertRedirect(self::DASHBOARD_PATH);

        Auth::logout();
        $this->post('/login', [
            'email' => 'dashboard@example.com',
            'password' => 'password',
        ])->assertRedirect(self::DASHBOARD_PATH);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(self::DASHBOARD_PATH)->assertRedirect('/login');
    }

    public function test_external_user_cannot_view_dashboard_or_see_its_navigation(): void
    {
        $external = User::factory()->create(['user_type' => 'external', 'is_active' => true]);

        $this->actingAs($external)
            ->get(self::DASHBOARD_PATH)
            ->assertRedirect(self::CUSTOMER_HISTORY_PATH)
            ->assertSessionHas('status', __('messages.authorization.dashboard_only'));

        $this->get(self::CUSTOMER_HISTORY_PATH)
            ->assertOk()
            ->assertDontSee('href="'.route('dashboard.index').'"', false);

        $this->get('/')->assertRedirect(self::CUSTOMER_HISTORY_PATH);
    }

    public function test_internal_dashboard_only_shows_its_accessible_groups(): void
    {
        $ownGroup = ResponsibilityGroup::query()->create(['name' => 'Own group', 'is_active' => true]);
        $otherGroup = ResponsibilityGroup::query()->create(['name' => 'Other group', 'is_active' => true]);
        $internal = User::factory()->create([
            'user_type' => 'internal',
            'responsibility_group_id' => $ownGroup->id,
            'is_active' => true,
        ]);
        $otherOwner = User::factory()->create([
            'user_type' => 'external',
            'responsibility_group_id' => $otherGroup->id,
            'is_active' => true,
        ]);

        foreach ([[$internal, 'DASH-OWN'], [$otherOwner, 'DASH-OTHER']] as [$owner, $customerNo]) {
            DB::table('customers')->insert([
                'CustomerNo' => $customerNo,
                'CustomerRefNo' => $customerNo,
                'Firstname' => 'Dashboard',
                'Lastname' => 'Customer',
                'Email' => strtolower($customerNo).'@example.com',
                'sysInsertUserId' => $owner->id,
                'sysInsertDateTime' => now(),
                'HmeterTransferStatus' => 'pending',
            ]);
        }

        $this->actingAs($internal)
            ->get(self::DASHBOARD_PATH)
            ->assertOk()
            ->assertViewHas('pending', 1)
            ->assertSee('open_customer=DASH-OWN', false)
            ->assertViewHas('groups', fn ($groups) => $groups->pluck('id')->contains($ownGroup->id)
                && ! $groups->pluck('id')->contains($otherGroup->id));
    }
}
