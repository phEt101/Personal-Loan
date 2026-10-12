<?php

namespace App\Modules\Dashboard\Http\Controllers;

use App\Modules\Settings\Models\ResponsibilityGroup;
use App\Modules\Settings\Services\CustomerAccessService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const OVERDUE_DAYS = 7;

    private const QUEUE_PER_PAGE_OPTIONS = [10, 25, 50];

    public function __construct(private readonly CustomerAccessService $customerAccess) {}

    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $groupIds = $user->isAdmin()
            ? null
            : $this->customerAccess->accessibleExternalGroupIds($user);
        $filters = $request->validate([
            'period' => ['nullable', Rule::in(['7', '30', '90'])],
            'per_page' => ['nullable', 'integer', Rule::in(self::QUEUE_PER_PAGE_OPTIONS)],
            'group_id' => [
                'nullable', 'integer',
                Rule::exists('responsibility_groups', 'id')->when(
                    $groupIds !== null,
                    fn ($rule) => $rule->whereIn('id', $groupIds)
                ),
            ],
        ]);

        $period = (int) ($filters['period'] ?? 30);
        $perPage = (int) ($filters['per_page'] ?? self::QUEUE_PER_PAGE_OPTIONS[0]);
        $groupId = isset($filters['group_id']) ? (int) $filters['group_id'] : null;
        $timezone = config('app.local_timezone', 'Asia/Bangkok');
        $today = CarbonImmutable::now($timezone);
        $periodStart = $today->subDays($period - 1)->startOfDay()->utc();
        $periodEnd = $today->endOfDay()->utc();
        $overdueBefore = $today->subDays(self::OVERDUE_DAYS)->startOfDay()->utc();

        $base = $this->customerQuery($groupId, $groupIds);
        $newToday = (clone $base)->whereBetween('customer.sysInsertDateTime', [
            $today->startOfDay()->utc(), $today->endOfDay()->utc(),
        ])->count();
        $newInPeriod = (clone $base)
            ->whereBetween('customer.sysInsertDateTime', [$periodStart, $periodEnd])
            ->count();
        $pending = (clone $base)->where('customer.HmeterTransferStatus', 'pending')->count();
        $transferred = (clone $base)->where('customer.HmeterTransferStatus', 'transferred')->count();
        $overdue = (clone $base)
            ->where('customer.HmeterTransferStatus', 'pending')
            ->where('customer.sysInsertDateTime', '<', $overdueBefore)
            ->count();

        $createdRows = (clone $base)
            ->whereBetween('customer.sysInsertDateTime', [$periodStart, $periodEnd])
            ->pluck('customer.sysInsertDateTime');
        $transferredRows = (clone $base)
            ->whereBetween('customer.HmeterTransferredAt', [$periodStart, $periodEnd])
            ->pluck('customer.HmeterTransferredAt');
        $trend = $this->trend($createdRows, $transferredRows, $period, $today, $timezone);

        $queue = (clone $base)
            ->where('customer.HmeterTransferStatus', 'pending')
            ->orderBy('customer.sysInsertDateTime')
            ->paginate($perPage, [
                'customer.CustomerNo', 'customer.Firstname', 'customer.Lastname',
                'customer.sysInsertDateTime', 'owner.first_name as OwnerFirstname',
                'owner.last_name as OwnerLastname', 'owner_group.name as GroupName',
            ], 'work_page')
            ->withQueryString();

        $performance = (clone $base)
            ->whereBetween('customer.sysInsertDateTime', [$periodStart, $periodEnd])
            ->groupBy('owner_group.id', 'owner_group.name')
            ->orderByDesc(DB::raw('COUNT(*)'))
            ->get([
                'owner_group.id', 'owner_group.name',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN customer.HmeterTransferStatus = 'pending' THEN 1 ELSE 0 END) as pending"),
                DB::raw("SUM(CASE WHEN customer.HmeterTransferStatus = 'transferred' THEN 1 ELSE 0 END) as transferred"),
            ]);

        $groups = ResponsibilityGroup::query()
            ->where('is_active', true)
            ->when($groupIds !== null, fn ($query) => $query->whereKey($groupIds))
            ->orderBy('name')
            ->get();

        return view('dashboard::index', compact(
            'groups', 'groupId', 'period', 'perPage', 'newToday', 'newInPeriod', 'pending',
            'transferred', 'overdue', 'trend', 'queue', 'performance'
        ) + [
            'overdueDays' => self::OVERDUE_DAYS,
            'perPageOptions' => self::QUEUE_PER_PAGE_OPTIONS,
        ]);
    }

    private function customerQuery(?int $groupId, ?Collection $groupIds): Builder
    {
        $query = DB::table('customers as customer')
            ->leftJoin('users as owner', 'owner.id', '=', 'customer.sysInsertUserId')
            ->leftJoin('responsibility_groups as owner_group', 'owner_group.id', '=', 'owner.responsibility_group_id')
            ->when($groupIds !== null, fn (Builder $query) => $query
                ->whereIn('owner.responsibility_group_id', $groupIds));

        return $query->when($groupId, fn (Builder $query, int $id) => $query
            ->where('owner.responsibility_group_id', $id));
    }

    private function trend(
        Collection $createdRows,
        Collection $transferredRows,
        int $period,
        CarbonImmutable $today,
        string $timezone
    ): array
    {
        $created = $transferred = [];
        foreach (range($period - 1, 0) as $daysAgo) {
            $key = $today->subDays($daysAgo)->format('Y-m-d');
            $created[$key] = 0;
            $transferred[$key] = 0;
        }

        foreach ($createdRows as $dateTime) {
            $key = CarbonImmutable::parse($dateTime, 'UTC')->timezone($timezone)->format('Y-m-d');
            if (array_key_exists($key, $created)) {
                $created[$key]++;
            }
        }
        foreach ($transferredRows as $dateTime) {
            $key = CarbonImmutable::parse($dateTime, 'UTC')->timezone($timezone)->format('Y-m-d');
            if (array_key_exists($key, $transferred)) {
                $transferred[$key]++;
            }
        }

        return ['labels' => array_keys($created), 'created' => array_values($created), 'transferred' => array_values($transferred)];
    }
}
