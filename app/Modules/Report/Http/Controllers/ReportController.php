<?php

namespace App\Modules\Report\Http\Controllers;

use App\Modules\Report\Services\ExcelReportExporter;
use App\Modules\Settings\Models\ResponsibilityGroup;
use App\Modules\Settings\Models\Role;
use App\Modules\Settings\Models\User;
use App\Modules\Settings\Services\CustomerAccessService;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    private const PER_PAGE_OPTIONS = [5, 10, 25, 50, 100];

    private const DEFAULT_PER_PAGE = 10;

    public function __construct(
        private readonly CustomerAccessService $customerAccess,
        private readonly ExcelReportExporter $excelExporter,
    ) {}

    public function index(Request $request): View
    {
        $filters = $this->validatedFilters($request);
        $report = $filters['report'] ?? 'transfers';
        $hasSearched = $request->boolean('submitted');
        $perPage = (int) ($filters['per_page'] ?? self::DEFAULT_PER_PAGE);
        $accessibleGroupIds = $this->customerAccess->accessibleExternalGroupIds($request->user());
        $queryParameters = $request->except(['page', 'partial']);
        $transferRows = $hasSearched && $report === 'transfers'
            ? $this->transferQuery($request, $filters)->paginate($perPage)->appends($queryParameters)
            : null;
        $customerRows = $hasSearched && $report === 'customers'
            ? $this->customerQuery($request, $filters)->paginate($perPage)->appends($queryParameters)
            : null;

        $viewData = [
            'report' => $report,
            'hasSearched' => $hasSearched,
            'perPage' => $perPage,
            'perPageOptions' => self::PER_PAGE_OPTIONS,
            'filters' => $filters,
            'transferRows' => $transferRows,
            'customerRows' => $customerRows,
            'groups' => ResponsibilityGroup::query()
                ->when($accessibleGroupIds !== null, fn ($query) => $query->whereKey($accessibleGroupIds))
                ->orderBy('name')
                ->get(),
            'externalUsers' => User::query()
                ->where('user_type', 'external')
                ->whereHas('role', fn ($query) => $query->where('slug', Role::USER_SLUG))
                ->when($accessibleGroupIds !== null, fn ($query) => $query
                    ->whereIn('responsibility_group_id', $accessibleGroupIds))
                ->orderBy('employee_code')
                ->get(),
            'internalUsers' => User::query()->where('user_type', 'internal')->orderBy('employee_code')->get(),
        ];

        return view($request->boolean('partial') ? 'report::_report_content' : 'report::index', $viewData);
    }

    public function export(Request $request): BinaryFileResponse
    {
        $filters = $this->validatedFilters($request);
        $report = $filters['report'] ?? 'transfers';
        $date = now()->timezone(config('app.local_timezone'))->format('Ymd-His');

        if ($report === 'customers') {
            $path = $this->excelExporter->create(
                __('report::messages.customer_tab'),
                $this->customerExportHeadings(),
                $this->customerExportRows($this->customerQuery($request, $filters)->get()),
            );

            return response()->download($path, "customer-report-{$date}.xlsx")->deleteFileAfterSend(true);
        }

        $path = $this->excelExporter->create(
            __('report::messages.transfer_tab'),
            $this->transferExportHeadings(),
            $this->transferExportRows($this->transferQuery($request, $filters)->get()),
        );

        return response()->download($path, "transfer-confirmation-report-{$date}.xlsx")->deleteFileAfterSend(true);
    }

    private function transferQuery(Request $request, array $filters): Builder
    {
        $query = DB::table('customers as customer')
            ->leftJoin('users as owner', 'owner.id', '=', 'customer.sysInsertUserId')
            ->leftJoin('roles as owner_role', 'owner_role.id', '=', 'owner.role_id')
            ->leftJoin('responsibility_groups as owner_group', 'owner_group.id', '=', 'owner.responsibility_group_id')
            ->leftJoin('users as confirmer', 'confirmer.id', '=', 'customer.HmeterTransferredBy')
            ->leftJoin('work_delegations as delegation', 'delegation.id', '=', 'customer.HmeterWorkDelegationId')
            ->leftJoin('users as delegator', 'delegator.id', '=', 'delegation.delegator_user_id')
            ->where('customer.HmeterTransferStatus', 'transferred')
            ->where('owner.user_type', 'external')
            ->where('owner_role.slug', Role::USER_SLUG);

        if (! $request->user()->isAdmin()) {
            $query->where(function (Builder $query) use ($request): void {
                $this->customerAccess->applyReadScope($query, $request->user(), 'customer');
                $query->orWhere('customer.HmeterTransferredBy', $request->user()->getAuthIdentifier());
            });
        }
        $this->applyCommonFilters($query, $filters, 'customer.HmeterTransferredAt');

        return $query
            ->when($filters['confirmer_id'] ?? null, fn (Builder $query, $id) => $query->where('customer.HmeterTransferredBy', $id))
            ->when(($filters['transfer_source'] ?? null) === 'direct', fn (Builder $query) => $query->whereNull('customer.HmeterWorkDelegationId'))
            ->when(($filters['transfer_source'] ?? null) === 'delegated', fn (Builder $query) => $query->whereNotNull('customer.HmeterWorkDelegationId'))
            ->select([
                'customer.CustomerNo', 'customer.Firstname', 'customer.Lastname', 'customer.HmeterTransferredAt',
                'owner.first_name as OwnerFirstname', 'owner.last_name as OwnerLastname',
                'owner_group.name as GroupName',
                'confirmer.first_name as ConfirmerFirstname', 'confirmer.last_name as ConfirmerLastname',
                'customer.HmeterWorkDelegationId',
                'delegator.first_name as DelegatorFirstname', 'delegator.last_name as DelegatorLastname',
            ])
            ->orderByDesc('customer.HmeterTransferredAt');
    }

    private function customerQuery(Request $request, array $filters): Builder
    {
        $query = DB::table('customers as customer')
            ->join('users as owner', 'owner.id', '=', 'customer.sysInsertUserId')
            ->join('roles as owner_role', 'owner_role.id', '=', 'owner.role_id')
            ->leftJoin('responsibility_groups as owner_group', 'owner_group.id', '=', 'owner.responsibility_group_id')
            ->where('owner.user_type', 'external')
            ->where('owner_role.slug', Role::USER_SLUG);

        $this->customerAccess->applyReadScope($query, $request->user(), 'customer');
        $this->applyCommonFilters($query, $filters, 'customer.sysInsertDateTime');

        return $query
            ->when($filters['transfer_status'] ?? null, fn (Builder $query, $status) => $query
                ->where('customer.HmeterTransferStatus', $status))
            ->select([
                'customer.CustomerNo', 'customer.Firstname', 'customer.Lastname',
                'customer.HmeterTransferStatus', 'customer.sysInsertDateTime', 'customer.HmeterTransferredAt',
                'owner.first_name as OwnerFirstname', 'owner.last_name as OwnerLastname',
                'owner_group.name as GroupName',
            ])
            ->orderByDesc('customer.sysInsertDateTime');
    }

    private function applyCommonFilters(Builder $query, array $filters, string $dateColumn): void
    {
        $timezone = config('app.local_timezone');

        $query
            ->when($filters['date_from'] ?? null, fn (Builder $query, $date) => $query
                ->where($dateColumn, '>=', Carbon::createFromFormat('Y-m-d', $date, $timezone)->startOfDay()->utc()))
            ->when($filters['date_to'] ?? null, fn (Builder $query, $date) => $query
                ->where($dateColumn, '<=', Carbon::createFromFormat('Y-m-d', $date, $timezone)->endOfDay()->utc()))
            ->when($filters['group_id'] ?? null, fn (Builder $query, $id) => $query->where('owner.responsibility_group_id', $id))
            ->when($filters['external_user_id'] ?? null, fn (Builder $query, $id) => $query->where('owner.id', $id));
    }

    private function validatedFilters(Request $request): array
    {
        $standardUserRoleId = Role::query()->where('slug', Role::USER_SLUG)->valueOrFail('id');
        $accessibleGroupIds = $this->customerAccess->accessibleExternalGroupIds($request->user());

        return $request->validate([
            'report' => ['nullable', Rule::in(['transfers', 'customers'])],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
            'group_id' => [
                'nullable',
                'integer',
                Rule::exists('responsibility_groups', 'id')->when(
                    $accessibleGroupIds !== null,
                    fn ($rule) => $rule->whereIn('id', $accessibleGroupIds)
                ),
            ],
            'external_user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query
                    ->where('user_type', 'external')
                    ->where('role_id', $standardUserRoleId)
                    ->when($accessibleGroupIds !== null, fn ($query) => $query
                        ->whereIn('responsibility_group_id', $accessibleGroupIds))
                    ->when($request->filled('group_id'), fn ($query) => $query
                        ->where('responsibility_group_id', $request->input('group_id')))),
            ],
            'confirmer_id' => ['nullable', 'integer', 'exists:users,id'],
            'transfer_source' => ['nullable', Rule::in(['direct', 'delegated'])],
            'transfer_status' => ['nullable', Rule::in(['pending', 'transferred'])],
            'per_page' => ['nullable', 'integer', Rule::in(self::PER_PAGE_OPTIONS)],
        ]);
    }

    private function transferExportHeadings(): array
    {
        return [
            __('report::messages.customer_no'),
            __('report::messages.customer'),
            __('report::messages.external_owner'),
            __('report::messages.group'),
            __('report::messages.confirmed_by'),
            __('report::messages.source'),
            __('report::messages.delegated_from'),
            __('report::messages.confirmed_at'),
        ];
    }

    private function transferExportRows(iterable $rows): iterable
    {
        foreach ($rows as $row) {
            yield [
                $row->CustomerNo,
                $this->fullName($row->Firstname, $row->Lastname),
                $this->fullName($row->OwnerFirstname, $row->OwnerLastname),
                $row->GroupName ?: __('report::messages.unassigned_group'),
                $this->fullName($row->ConfirmerFirstname, $row->ConfirmerLastname),
                $row->HmeterWorkDelegationId ? __('report::messages.delegated') : __('report::messages.direct'),
                $row->HmeterWorkDelegationId ? $this->fullName($row->DelegatorFirstname, $row->DelegatorLastname) : '-',
                $this->localDateTime($row->HmeterTransferredAt),
            ];
        }
    }

    private function customerExportHeadings(): array
    {
        return [
            __('report::messages.customer_no'),
            __('report::messages.customer'),
            __('report::messages.external_owner'),
            __('report::messages.group'),
            __('report::messages.transfer_status'),
            __('report::messages.created_at'),
            __('report::messages.confirmed_at'),
        ];
    }

    private function customerExportRows(iterable $rows): iterable
    {
        foreach ($rows as $row) {
            yield [
                $row->CustomerNo,
                $this->fullName($row->Firstname, $row->Lastname),
                $this->fullName($row->OwnerFirstname, $row->OwnerLastname),
                $row->GroupName ?: __('report::messages.unassigned_group'),
                $row->HmeterTransferStatus === 'transferred'
                    ? __('report::messages.transferred')
                    : __('report::messages.pending'),
                $this->localDateTime($row->sysInsertDateTime),
                $this->localDateTime($row->HmeterTransferredAt),
            ];
        }
    }

    private function fullName(?string $firstName, ?string $lastName): string
    {
        return trim(($firstName ?? '').' '.($lastName ?? '')) ?: '-';
    }

    private function localDateTime(?string $value): string
    {
        if (! $value) {
            return '-';
        }

        return Carbon::parse($value, 'UTC')
            ->timezone(config('app.local_timezone'))
            ->format('d/m/Y H:i');
    }
}
