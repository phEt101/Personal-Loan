<?php

namespace App\Modules\CustomerHistory\Http\Controllers;

use App\Models\User;
use App\Modules\CustomerHistory\Services\CustomerAccessService;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CustomerHistoryController extends Controller
{
    private const PER_PAGE_OPTIONS = [5, 10, 25, 50, 100];

    private const DEFAULT_PER_PAGE = 10;

    public function __construct(private readonly CustomerAccessService $customerAccess) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $access = $this->customerListAccess($user);
        $filters = $this->customerListFilters($request, $access);
        $query = $this->customerListQuery($user, $access);

        $this->applyCustomerFilters($query, $filters, $access['canViewCreator']);
        $this->applyCustomerSorting($query, $filters['sort'], $filters['direction']);

        $customers = $query
            ->orderBy('customer.id', $filters['direction'])
            ->paginate($filters['perPage'])
            ->withQueryString();
        $listData = $this->customerListData($customers, $user, $access, $filters);

        if ($request->boolean('partial')) {
            return view('customerhistory::_customer_list', $listData);
        }

        return view('customerhistory::index', array_merge($listData, $this->customerFormData()));
    }

    private function customerListAccess(User $user): array
    {
        $isInternalUser = $user->user_type === 'internal';
        $isExternalManager = $user->user_type === 'external' && $user->isManager();

        return [
            'isInternalUser' => $isInternalUser,
            'isExternalManager' => $isExternalManager,
            'canViewCreator' => $isInternalUser || $isExternalManager,
            'canCreateCustomer' => ! $isExternalManager,
        ];
    }

    private function customerListFilters(Request $request, array $access): array
    {
        $perPage = (int) $request->query('per_page', self::DEFAULT_PER_PAGE);
        if (! in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            $perPage = self::DEFAULT_PER_PAGE;
        }

        $allowedSorts = ['customer_no', 'customer_name', 'mobile', 'created_at'];
        if ($access['canViewCreator']) {
            $allowedSorts[] = 'created_by';
        }

        $creatorType = $request->query('creator_type');
        $requestedSort = $request->query('sort');

        return [
            'perPage' => $perPage,
            'perPageOptions' => self::PER_PAGE_OPTIONS,
            'search' => mb_substr(trim((string) $request->query('q', '')), 0, 100),
            'dateFrom' => $this->validDateFilter($request->query('date_from')),
            'dateTo' => $this->validDateFilter($request->query('date_to')),
            'creatorType' => $access['isInternalUser']
                && in_array($creatorType, ['internal', 'external'], true)
                    ? $creatorType
                    : '',
            'sort' => in_array($requestedSort, $allowedSorts, true) ? $requestedSort : 'created_at',
            'direction' => $request->query('direction') === 'asc' ? 'asc' : 'desc',
        ];
    }

    private function customerListQuery(User $user, array $access): Builder
    {
        $query = DB::table('customers as customer')
            ->select([
                'customer.CustomerNo',
                'customer.Firstname',
                'customer.Lastname',
                'customer.Mobile',
                'customer.sysInsertUserId',
                'customer.sysInsertDateTime',
                'customer.HmeterTransferStatus',
            ]);

        if ($access['canViewCreator']) {
            $query
                ->leftJoin('users as creator', 'creator.id', '=', 'customer.sysInsertUserId')
                ->addSelect([
                    'creator.employee_code as CreatorEmployeeCode',
                    'creator.first_name as CreatorFirstname',
                    'creator.last_name as CreatorLastname',
                ]);
        }

        $this->customerAccess->applyReadScope($query, $user, 'customer');

        return $query;
    }

    private function applyCustomerFilters(Builder $query, array $filters, bool $canViewCreator): void
    {
        if ($filters['dateFrom'] !== '') {
            $query->whereDate('customer.sysInsertDateTime', '>=', $filters['dateFrom']);
        }
        if ($filters['dateTo'] !== '') {
            $query->whereDate('customer.sysInsertDateTime', '<=', $filters['dateTo']);
        }
        if ($filters['creatorType'] !== '') {
            $query->where('creator.user_type', $filters['creatorType']);
        }
        if ($filters['search'] !== '') {
            $this->applyCustomerSearch($query, $filters['search'], $canViewCreator);
        }
    }

    private function applyCustomerSearch(Builder $query, string $search, bool $canViewCreator): void
    {
        $query->where(function (Builder $query) use ($search, $canViewCreator) {
            $pattern = "%{$search}%";
            $query
                ->where('customer.CustomerNo', 'like', $pattern)
                ->orWhere('customer.Firstname', 'like', $pattern)
                ->orWhere('customer.Lastname', 'like', $pattern)
                ->orWhere('customer.Mobile', 'like', $pattern);

            $nameParts = preg_split('/\s+/', $search, 2);
            if (count($nameParts) === 2) {
                $query->orWhere(function (Builder $query) use ($nameParts) {
                    $query->where('customer.Firstname', 'like', "%{$nameParts[0]}%")
                        ->where('customer.Lastname', 'like', "%{$nameParts[1]}%");
                });
            }

            if ($canViewCreator) {
                $query
                    ->orWhere('creator.employee_code', 'like', $pattern)
                    ->orWhere('creator.first_name', 'like', $pattern)
                    ->orWhere('creator.last_name', 'like', $pattern);
            }
        });
    }

    private function applyCustomerSorting(Builder $query, string $sort, string $direction): void
    {
        match ($sort) {
            'customer_no' => $query->orderBy('customer.CustomerNo', $direction),
            'customer_name' => $query
                ->orderBy('customer.Firstname', $direction)
                ->orderBy('customer.Lastname', $direction),
            'mobile' => $query->orderBy('customer.Mobile', $direction),
            'created_by' => $query
                ->orderBy('creator.first_name', $direction)
                ->orderBy('creator.last_name', $direction)
                ->orderBy('creator.employee_code', $direction),
            default => $query->orderBy('customer.sysInsertDateTime', $direction),
        };
    }

    private function customerListData(
        LengthAwarePaginator $customers,
        User $user,
        array $access,
        array $filters
    ): array {
        return [
            'customers' => $customers,
            'paginationPages' => $this->paginationPages($customers->currentPage(), $customers->lastPage()),
            'isInternalUser' => $access['isInternalUser'],
            'isExternalManager' => $access['isExternalManager'],
            'canViewCreator' => $access['canViewCreator'],
            'canCreateCustomer' => $access['canCreateCustomer'],
            'currentUserId' => (int) $user->getAuthIdentifier(),
            ...$filters,
            'hasActiveFilters' => $filters['search'] !== ''
                || $filters['dateFrom'] !== ''
                || $filters['dateTo'] !== ''
                || $filters['creatorType'] !== '',
        ];
    }

    private function customerFormData(): array
    {
        $documentTypes = DB::table('document_types')
            ->where('Active', true)
            ->orderBy('SortOrder')
            ->orderBy('id')
            ->get(['id', 'DocumentTypeNameTh', 'DocumentTypeNameEn']);

        return [
            'titles' => DB::table('titles')->where('Active', true)->whereNotNull('TitleDesc')
                ->where('TitleDesc', '<>', '')->orderBy('TitleCode')->get(['TitleCode', 'TitleDesc']),
            'identityCardTypes' => DB::table('identity_card_types')->orderBy('IdentityCardTypeCode')
                ->get(['IdentityCardTypeCode', 'IdentityCardTypeDesc']),
            'maritalStatuses' => DB::table('marital_statuses')->orderBy('MaritalStatusCode')
                ->get(['MaritalStatusCode', 'MaritalStatusName']),
            'genders' => DB::table('genders')->orderBy('GenderId')->get(['GenderId', 'GenderDesc']),
            'occupations' => DB::table('occupations')->orderBy('OccupationCode')
                ->get(['OccupationCode', 'OccupationDesc', 'IsOtherOccupation']),
            'workingConditions' => DB::table('working_conditions')->orderBy('WorkingConditionId')
                ->get(['WorkingConditionId', 'Description', 'IsRequireOccupation']),
            'businessTypes' => DB::table('type_of_businesses')->where('Active', true)->orderBy('TypeOfBusinessId')
                ->get(['TypeOfBusinessId', 'TypeOfBusinessName']),
            'addressTypes' => DB::table('address_types')->orderBy('AddressTypeCode')
                ->get(['AddressTypeCode', 'AddressTypeDesc']),
            'banks' => DB::table('banks')->where('Active', true)->orderBy('BankCode')
                ->get(['BankCode', 'BankDesc']),
            'provinces' => DB::table('provinces')->orderBy('ProvinceDesc')
                ->get(['ProvinceCode', 'ProvinceDesc']),
            'phoneTypes' => DB::table('phone_types')->orderBy('PhoneTypeCode')
                ->get(['PhoneTypeCode', 'PhoneTypeDesc']),
            'documentTypes' => $documentTypes,
            'customerFormConfig' => $this->customerFormConfig($documentTypes),
        ];
    }

    private function customerFormConfig(Collection $documentTypes): array
    {
        return [
            'urls' => [
                'districts' => route('customer-history.locations.districts'),
                'subDistricts' => route('customer-history.locations.sub-districts'),
                'identityCardCheck' => route('customer-history.identity-card.check'),
            ],
            'identityNumberLabels' => [
                1 => __('customerhistory::messages.form.personal.identity_card'),
                2 => __('customerhistory::messages.form.personal.passport_number'),
                3 => __('customerhistory::messages.form.personal.government_card_number'),
                4 => __('customerhistory::messages.form.personal.other_document_number'),
                5 => __('customerhistory::messages.form.personal.tax_id_number'),
            ],
            'attachmentTypes' => $documentTypes->mapWithKeys(fn ($documentType) => [
                $documentType->id => app()->getLocale() === 'th'
                    ? $documentType->DocumentTypeNameTh
                    : $documentType->DocumentTypeNameEn,
            ]),
            'messages' => $this->customerFormMessages(),
        ];
    }

    private function customerFormMessages(): array
    {
        return [
            'searchOption' => __('customerhistory::messages.form.search_option'),
            'noSearchResults' => __('customerhistory::messages.form.no_search_results'),
            'noOptions' => __('customerhistory::messages.form.no_options'),
            'required' => __('customerhistory::messages.form.required'),
            'invalidNationalId' => __('customerhistory::messages.form.personal.invalid_national_id'),
            'identityDocumentNumber' => __('customerhistory::messages.form.personal.identity_document_number'),
            'saveFailed' => __('customerhistory::messages.form.save_failed'),
            'addressRequired' => __('customerhistory::messages.form.address.address_required'),
            'selectOption' => __('customerhistory::messages.form.select_option'),
            'editAddress' => __('customerhistory::messages.form.address.edit'),
            'deleteAddress' => __('customerhistory::messages.form.address.delete'),
            'addAddress' => __('customerhistory::messages.form.address.confirm_add'),
            'saveAddress' => __('customerhistory::messages.form.address.confirm_edit'),
            'addressNoData' => __('customerhistory::messages.form.address.no_data'),
            'phoneRequired' => __('customerhistory::messages.form.contact.phone_required'),
            'editPhone' => __('customerhistory::messages.form.contact.edit'),
            'deletePhone' => __('customerhistory::messages.form.contact.delete'),
            'addPhone' => __('customerhistory::messages.form.contact.confirm_add'),
            'savePhone' => __('customerhistory::messages.form.contact.confirm_edit'),
            'phoneNoData' => __('customerhistory::messages.form.contact.no_data'),
            'detailTitle' => __('customerhistory::messages.index.detail_title'),
            'editTitle' => __('customerhistory::messages.form.edit_title'),
            'detailLoadFailed' => __('customerhistory::messages.index.detail_load_failed'),
            'duplicateNationalId' => __('customerhistory::messages.form.personal.duplicate_national_id'),
            'identityCheckFailed' => __('customerhistory::messages.form.personal.identity_check_failed'),
            'removeAttachment' => __('customerhistory::messages.form.attachments.remove'),
            'noAttachments' => __('customerhistory::messages.form.attachments.no_files'),
            'invalidAttachment' => __('customerhistory::messages.form.attachments.invalid_file'),
            'editAttachment' => __('customerhistory::messages.form.attachments.edit'),
            'addAttachment' => __('customerhistory::messages.form.attachments.confirm_add'),
            'saveAttachment' => __('customerhistory::messages.form.attachments.confirm_edit'),
            'previewAttachment' => __('customerhistory::messages.form.attachments.preview'),
            'downloadAllAttachments' => __('customerhistory::messages.form.attachments.download_all'),
            'confirmHmeterTransfer' => __('customerhistory::messages.transfer.confirm_button'),
            'confirmHmeterTransferPrompt' => __('customerhistory::messages.transfer.confirm_prompt'),
            'transferPending' => __('customerhistory::messages.transfer.pending'),
            'transferCompleted' => __('customerhistory::messages.transfer.completed'),
            'transferredBy' => __('customerhistory::messages.transfer.transferred_by'),
            'transferredAt' => __('customerhistory::messages.transfer.transferred_at'),
            'attachmentsPurgeAfter' => __('customerhistory::messages.transfer.attachments_purge_after'),
            'attachmentsPurged' => __('customerhistory::messages.transfer.attachments_purged'),
            'attachmentsPurgedEmpty' => __('customerhistory::messages.form.attachments.purged_empty'),
            'attachmentsPurgedAt' => __('customerhistory::messages.form.attachments.purged_at'),
        ];
    }

    private function validDateFilter(mixed $value): string
    {
        $date = trim((string) $value);
        if ($date === '') {
            return '';
        }

        try {
            $parsedDate = Carbon::createFromFormat('Y-m-d', $date);

            return $parsedDate->format('Y-m-d') === $date ? $date : '';
        } catch (\Throwable) {
            return '';
        }
    }

    private function paginationPages(int $currentPage, int $lastPage): array
    {
        if ($lastPage <= 1) {
            return [1];
        }

        $pages = [1, $lastPage];
        for ($page = max(1, $currentPage - 2); $page <= min($lastPage, $currentPage + 2); $page++) {
            $pages[] = $page;
        }
        sort($pages);

        return array_values(array_unique($pages));
    }
}
