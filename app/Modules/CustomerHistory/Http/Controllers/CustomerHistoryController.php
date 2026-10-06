<?php

namespace App\Modules\CustomerHistory\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CustomerHistoryController extends Controller
{
    private const TRANSFER_PENDING = 'pending';

    private const TRANSFERRED = 'transferred';

    private const ATTACHMENT_RETENTION_DAYS = 30;

    public function index(Request $request)
    {
        $user = $request->user();
        $isInternalUser = $user->user_type === 'internal';
        $isExternalManager = $user->user_type === 'external' && $user->isManager();
        $canViewCreator = $isInternalUser || $isExternalManager;
        $perPageOptions = [5, 10, 25, 50, 100];
        $perPage = (int) $request->query('per_page', 10);

        if (! in_array($perPage, $perPageOptions, true)) {
            $perPage = 10;
        }

        $customersQuery = DB::table('customers as customer')
            ->select([
                'customer.CustomerNo',
                'customer.Firstname',
                'customer.Lastname',
                'customer.Mobile',
                'customer.sysInsertUserId',
                'customer.sysInsertDateTime',
                'customer.HmeterTransferStatus',
            ]);

        if (! $canViewCreator) {
            $customersQuery->where('customer.sysInsertUserId', $user->getAuthIdentifier());
        } else {
            $customersQuery
                ->leftJoin('users as creator', 'creator.id', '=', 'customer.sysInsertUserId')
                ->addSelect([
                    'creator.employee_code as CreatorEmployeeCode',
                    'creator.first_name as CreatorFirstname',
                    'creator.last_name as CreatorLastname',
                ]);

            if ($isExternalManager) {
                $customersQuery->where('creator.user_type', 'external');
            }
        }

        $search = mb_substr(trim((string) $request->query('q', '')), 0, 100);
        $dateFrom = $this->validDateFilter($request->query('date_from'));
        $dateTo = $this->validDateFilter($request->query('date_to'));
        $creatorType = $isInternalUser
            && in_array($request->query('creator_type'), ['internal', 'external'], true)
                ? $request->query('creator_type')
                : '';
        $allowedSorts = ['customer_no', 'customer_name', 'mobile', 'created_at'];

        if ($canViewCreator) {
            $allowedSorts[] = 'created_by';
        }

        $sort = in_array($request->query('sort'), $allowedSorts, true)
            ? $request->query('sort')
            : 'created_at';
        $direction = $request->query('direction') === 'asc' ? 'asc' : 'desc';

        if ($dateFrom !== '') {
            $customersQuery->whereDate('customer.sysInsertDateTime', '>=', $dateFrom);
        }

        if ($dateTo !== '') {
            $customersQuery->whereDate('customer.sysInsertDateTime', '<=', $dateTo);
        }

        if ($creatorType !== '') {
            $customersQuery->where('creator.user_type', $creatorType);
        }

        if ($search !== '') {
            $customersQuery->where(function ($query) use ($search, $canViewCreator) {
                $query
                    ->where('customer.CustomerNo', 'like', "%{$search}%")
                    ->orWhere('customer.Firstname', 'like', "%{$search}%")
                    ->orWhere('customer.Lastname', 'like', "%{$search}%")
                    ->orWhere('customer.Mobile', 'like', "%{$search}%");

                $nameParts = preg_split('/\s+/', $search, 2);
                if (count($nameParts) === 2) {
                    $query->orWhere(function ($query) use ($nameParts) {
                        $query->where('customer.Firstname', 'like', "%{$nameParts[0]}%")
                            ->where('customer.Lastname', 'like', "%{$nameParts[1]}%");
                    });
                }

                if ($canViewCreator) {
                    $query
                        ->orWhere('creator.employee_code', 'like', "%{$search}%")
                        ->orWhere('creator.first_name', 'like', "%{$search}%")
                        ->orWhere('creator.last_name', 'like', "%{$search}%");
                }
            });
        }

        match ($sort) {
            'customer_no' => $customersQuery->orderBy('customer.CustomerNo', $direction),
            'customer_name' => $customersQuery
                ->orderBy('customer.Firstname', $direction)
                ->orderBy('customer.Lastname', $direction),
            'mobile' => $customersQuery->orderBy('customer.Mobile', $direction),
            'created_by' => $customersQuery
                ->orderBy('creator.first_name', $direction)
                ->orderBy('creator.last_name', $direction)
                ->orderBy('creator.employee_code', $direction),
            default => $customersQuery->orderBy('customer.sysInsertDateTime', $direction),
        };

        $customers = $customersQuery
            ->orderBy('customer.id', $direction)
            ->paginate($perPage)
            ->withQueryString();

        $customerListData = [
            'customers' => $customers,
            'paginationPages' => $this->paginationPages($customers->currentPage(), $customers->lastPage()),
            'isInternalUser' => $isInternalUser,
            'isExternalManager' => $isExternalManager,
            'canViewCreator' => $canViewCreator,
            'canCreateCustomer' => ! $isExternalManager,
            'currentUserId' => (int) $user->getAuthIdentifier(),
            'perPage' => $perPage,
            'perPageOptions' => $perPageOptions,
            'search' => $search,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'creatorType' => $creatorType,
            'sort' => $sort,
            'direction' => $direction,
            'hasActiveFilters' => $search !== '' || $dateFrom !== '' || $dateTo !== '' || $creatorType !== '',
        ];

        if ($request->boolean('partial')) {
            return view('customerhistory::_customer_list', $customerListData);
        }

        $documentTypes = DB::table('document_types')
            ->where('Active', true)
            ->orderBy('SortOrder')
            ->orderBy('id')
            ->get(['id', 'DocumentTypeNameTh', 'DocumentTypeNameEn']);

        return view('customerhistory::index', array_merge($customerListData, [
            'titles' => DB::table('titles')
                ->where('Active', true)
                ->whereNotNull('TitleDesc')
                ->where('TitleDesc', '<>', '')
                ->orderBy('TitleCode')
                ->get(['TitleCode', 'TitleDesc']),
            'identityCardTypes' => DB::table('identity_card_types')
                ->orderBy('IdentityCardTypeCode')
                ->get(['IdentityCardTypeCode', 'IdentityCardTypeDesc']),
            'maritalStatuses' => DB::table('marital_statuses')
                ->orderBy('MaritalStatusCode')
                ->get(['MaritalStatusCode', 'MaritalStatusName']),
            'genders' => DB::table('genders')
                ->orderBy('GenderId')
                ->get(['GenderId', 'GenderDesc']),
            'occupations' => DB::table('occupations')
                ->orderBy('OccupationCode')
                ->get(['OccupationCode', 'OccupationDesc', 'IsOtherOccupation']),
            'workingConditions' => DB::table('working_conditions')
                ->orderBy('WorkingConditionId')
                ->get(['WorkingConditionId', 'Description', 'IsRequireOccupation']),
            'businessTypes' => DB::table('type_of_businesses')
                ->where('Active', true)
                ->orderBy('TypeOfBusinessId')
                ->get(['TypeOfBusinessId', 'TypeOfBusinessName']),
            'addressTypes' => DB::table('address_types')
                ->orderBy('AddressTypeCode')
                ->get(['AddressTypeCode', 'AddressTypeDesc']),
            'banks' => DB::table('banks')
                ->where('Active', true)
                ->orderBy('BankCode')
                ->get(['BankCode', 'BankDesc']),
            'provinces' => DB::table('provinces')
                ->orderBy('ProvinceDesc')
                ->get(['ProvinceCode', 'ProvinceDesc']),
            'phoneTypes' => DB::table('phone_types')
                ->orderBy('PhoneTypeCode')
                ->get(['PhoneTypeCode', 'PhoneTypeDesc']),
            'documentTypes' => $documentTypes,
            'customerFormConfig' => [
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
                'messages' => [
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
                ],
            ],
        ]));
    }

    public function districts(Request $request): JsonResponse
    {
        $provinceCode = (string) $request->query('province_code', '');

        return response()->json(
            DB::table('districts')
                ->where('ProvinceCode', $provinceCode)
                ->orderBy('DistrictDesc')
                ->get(['DistrictCode', 'DistrictDesc'])
        );
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

    public function subDistricts(Request $request): JsonResponse
    {
        $provinceCode = (string) $request->query('province_code', '');
        $districtCode = (string) $request->query('district_code', '');

        return response()->json(
            DB::table('sub_districts')
                ->where('ProvinceCode', $provinceCode)
                ->where('DistrictCode', $districtCode)
                ->orderBy('SubDistrictDesc')
                ->get(['SubDistrictCode', 'SubDistrictDesc', 'Zipcode'])
        );
    }

    public function checkIdentityCard(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'identity_card_id' => ['required', 'digits:13'],
        ]);

        $query = DB::table('customers')
            ->where('IdentityCardTypeCode', 1)
            ->where('IdentityCardId', $validated['identity_card_id']);

        if ($request->filled('customer_no')) {
            $query->where('CustomerNo', '<>', (string) $request->query('customer_no'));
        }

        return response()->json(['exists' => $query->exists()]);
    }

    public function show(Request $request, string $customerNo): JsonResponse
    {
        $user = $request->user();
        $customerQuery = DB::table('customers as customer')
            ->leftJoin('users as creator', 'creator.id', '=', 'customer.sysInsertUserId')
            ->leftJoin('users as transfer_user', 'transfer_user.id', '=', 'customer.HmeterTransferredBy')
            ->leftJoin('identity_card_types as identity_type', 'identity_type.IdentityCardTypeCode', '=', 'customer.IdentityCardTypeCode')
            ->leftJoin('genders as gender', 'gender.GenderId', '=', 'customer.GenderCode')
            ->leftJoin('working_conditions as working_condition', 'working_condition.WorkingConditionId', '=', 'customer.WorkingConditionId')
            ->leftJoin('banks as bank', 'bank.BankCode', '=', 'customer.BankCode')
            ->where('customer.CustomerNo', $customerNo)
            ->select([
                'customer.*',
                'identity_type.IdentityCardTypeDesc',
                'gender.GenderDesc',
                'working_condition.Description as WorkingConditionDesc',
                'bank.BankDesc',
                'creator.employee_code as CreatorEmployeeCode',
                'creator.first_name as CreatorFirstname',
                'creator.last_name as CreatorLastname',
                'transfer_user.first_name as HmeterTransferredByFirstname',
                'transfer_user.last_name as HmeterTransferredByLastname',
            ]);

        $this->restrictCustomerReadAccess($customerQuery, $user, 'customer');

        $customer = $customerQuery->firstOrFail();

        $email = DB::table('customer_emails')->where('CustomerNo', $customerNo)->orderBy('EmailId')->first();
        $remark = DB::table('customer_remarks')->where('CustomerNo', $customerNo)->orderBy('RemarkId')->first();

        return response()->json([
            'customer' => $customer,
            'addresses' => DB::table('customer_addresses')->where('CustomerNo', $customerNo)->orderBy('AddressId')->get(),
            'phones' => DB::table('customer_phones')->where('CustomerNo', $customerNo)->orderBy('PhoneId')->get(),
            'email_remark' => $email?->Remark,
            'comment' => $remark?->Comment,
            'hmeter_transfer' => [
                'status' => $customer->HmeterTransferStatus,
                'transferred_at' => $this->utcDateTime($customer->HmeterTransferredAt),
                'transferred_by' => trim(($customer->HmeterTransferredByFirstname ?? '').' '.($customer->HmeterTransferredByLastname ?? '')) ?: null,
                'attachment_purge_after' => $this->utcDateTime($customer->AttachmentPurgeAfter),
                'attachments_purged_at' => $this->utcDateTime($customer->AttachmentsPurgedAt),
                'can_confirm' => $user->user_type === 'internal' && $customer->HmeterTransferStatus === self::TRANSFER_PENDING,
                'confirm_url' => route('customer-history.hmeter-transfer', $customerNo),
                'visible' => $user->user_type === 'internal',
            ],
            'download_all_attachments_url' => route('customer-history.attachments.download-all', $customerNo),
            'attachments' => DB::table('customer_attachments as attachment')
                ->join('document_types as document_type', 'document_type.id', '=', 'attachment.DocumentTypeId')
                ->where('attachment.CustomerNo', $customerNo)
                ->orderBy('document_type.SortOrder')
                ->orderBy('attachment.id')
                ->select('attachment.*')
                ->get()
                ->map(fn ($attachment) => [
                    'id' => $attachment->id,
                    'document_name' => $attachment->DocumentName ?: $attachment->OriginalName,
                    'document_type_id' => $attachment->DocumentTypeId,
                    'original_name' => $attachment->OriginalName,
                    'mime_type' => $attachment->MimeType,
                    'file_size' => $attachment->FileSize,
                    'preview_url' => route('customer-history.attachments.download', $attachment->id),
                ]),
        ]);
    }

    public function downloadAttachment(Request $request, int $attachment)
    {
        $record = DB::table('customer_attachments')->where('id', $attachment)->firstOrFail();
        $customerQuery = DB::table('customers')->where('CustomerNo', $record->CustomerNo);

        $this->restrictCustomerReadAccess($customerQuery, $request->user());

        $customerQuery->firstOrFail();
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');
        abort_unless($disk->exists($record->FilePath), 404);

        return $disk->response($record->FilePath, $record->OriginalName, [], 'inline');
    }

    public function downloadAllAttachments(Request $request, string $customerNo)
    {
        $customerQuery = DB::table('customers')->where('CustomerNo', $customerNo);

        $this->restrictCustomerReadAccess($customerQuery, $request->user());

        $customerQuery->firstOrFail();
        $attachments = DB::table('customer_attachments')
            ->where('CustomerNo', $customerNo)
            ->orderBy('id')
            ->get();
        abort_if($attachments->isEmpty(), 404);

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');
        $zipPath = tempnam(sys_get_temp_dir(), 'customer-attachments-');
        abort_if($zipPath === false, 500);

        $zip = new \ZipArchive;
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            @unlink($zipPath);
            abort(500);
        }

        $fileCount = 0;
        foreach ($attachments as $index => $attachment) {
            if (! $disk->exists($attachment->FilePath)) {
                continue;
            }

            $originalName = basename(str_replace('\\', '/', $attachment->OriginalName));
            $extension = pathinfo($originalName, PATHINFO_EXTENSION);
            $documentName = trim((string) preg_replace(
                '/[\\/:*?"<>|\x00-\x1F]+/u',
                '-',
                $attachment->DocumentName ?: pathinfo($originalName, PATHINFO_FILENAME)
            ));
            $documentName = trim((string) preg_replace('/\s+/u', ' ', $documentName), '. ');
            $zipName = sprintf(
                '%02d-%s%s',
                $index + 1,
                $documentName ?: 'attachment-'.$attachment->id,
                $extension !== '' ? '.'.strtolower($extension) : ''
            );
            if ($zip->addFile($disk->path($attachment->FilePath), $zipName)) {
                $fileCount++;
            }
        }

        $zip->close();
        if ($fileCount === 0) {
            @unlink($zipPath);
            abort(404);
        }

        return response()
            ->download($zipPath, "customer-{$customerNo}-documents.zip")
            ->deleteFileAfterSend(true);
    }

    public function confirmHmeterTransfer(Request $request, string $customerNo): JsonResponse
    {
        abort_unless($request->user()->user_type === 'internal', 403);

        $now = now();
        $updated = DB::table('customers')
            ->where('CustomerNo', $customerNo)
            ->where('HmeterTransferStatus', self::TRANSFER_PENDING)
            ->update([
                'HmeterTransferStatus' => self::TRANSFERRED,
                'HmeterTransferredAt' => $now,
                'HmeterTransferredBy' => $request->user()->getAuthIdentifier(),
                'AttachmentPurgeAfter' => $now->copy()->addDays(self::ATTACHMENT_RETENTION_DAYS),
                'AttachmentsPurgedAt' => null,
            ]);

        if ($updated === 0) {
            abort_unless(DB::table('customers')->where('CustomerNo', $customerNo)->exists(), 404);

            return response()->json([
                'message' => __('customerhistory::messages.transfer.already_transferred'),
            ], 409);
        }

        return response()->json([
            'message' => __('customerhistory::messages.transfer.confirmed'),
            'hmeter_transfer' => [
                'status' => self::TRANSFERRED,
                'transferred_at' => $now->toISOString(),
                'transferred_by' => $request->user()->full_name,
                'attachment_purge_after' => $now->copy()->addDays(self::ATTACHMENT_RETENTION_DAYS)->toISOString(),
                'attachments_purged_at' => null,
                'can_confirm' => false,
                'confirm_url' => route('customer-history.hmeter-transfer', $customerNo),
                'visible' => true,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if ($request->user()->user_type === 'external' && $request->user()->isManager()) {
            return response()->json([
                'message' => __('customerhistory::messages.manager_read_only'),
            ], 403);
        }

        foreach (['MonthlyIncomeAmount', 'MonthlyExpenseAmount', 'YearlyBonusAmount'] as $field) {
            if ($request->filled($field)) {
                $request->merge([$field => str_replace(',', '', (string) $request->input($field))]);
            }
        }

        $request->merge([
            'AddressItems' => json_decode((string) $request->input('Addresses', ''), true),
            'PhoneItems' => json_decode((string) $request->input('Phones', ''), true),
        ]);

        $validated = $request->validate([
            'TitleCode' => ['required', Rule::exists('titles', 'TitleCode')],
            'Firstname' => ['required', 'string', 'max:100'],
            'Lastname' => ['required', 'string', 'max:50'],
            'Nickname' => ['nullable', 'string', 'max:10'],
            'GenderCode' => ['required', Rule::exists('genders', 'GenderId')],
            'BirthDate' => ['required', 'date'],
            'IdentityCardTypeCode' => ['required', Rule::exists('identity_card_types', 'IdentityCardTypeCode')],
            'IdentityCardId' => ['required', 'string', 'max:20'],
            'IdentityCardIssuer' => ['nullable', 'string', 'max:100'],
            'IdentityCardEffectiveDate' => ['nullable', 'date'],
            'IdentityCardExpireDate' => ['nullable', 'date', 'after_or_equal:IdentityCardEffectiveDate'],
            'Nationality' => ['nullable', 'string', 'max:50'],
            'Race' => ['nullable', 'string', 'max:50'],
            'MaritalStatusCode' => ['required', Rule::exists('marital_statuses', 'MaritalStatusCode')],
            'WorkingConditionId' => ['required', Rule::exists('working_conditions', 'WorkingConditionId')],
            'OccupationCode' => ['nullable', Rule::exists('occupations', 'OccupationCode')],
            'TypeOfBusinessId' => ['nullable', Rule::exists('type_of_businesses', 'TypeOfBusinessId')],
            'OtherOccupationDesc' => ['nullable', 'string', 'max:255'],
            'AddressTypeCode' => ['nullable', Rule::exists('address_types', 'AddressTypeCode')],
            'BankCode' => ['nullable', Rule::exists('banks', 'BankCode')],
            'BankBookBranch' => ['nullable', 'string', 'max:50'],
            'BankBookCode' => ['nullable', 'string', 'max:20'],
            'Addresses' => ['required', 'json'],
            'AddressItems' => ['required', 'array', 'min:1'],
            'AddressItems.*.AddressId' => ['required', 'integer', 'min:1', 'distinct'],
            'AddressItems.*.AddressLine1' => ['required', 'string', 'max:100'],
            'AddressItems.*.AddressLine2' => ['nullable', 'string', 'max:100'],
            'AddressItems.*.ProvinceCode' => ['required', Rule::exists('provinces', 'ProvinceCode')],
            'AddressItems.*.ProvinceDesc' => ['required', 'string', 'max:150'],
            'AddressItems.*.DistrictCode' => ['required', 'string', 'max:5'],
            'AddressItems.*.DistrictDesc' => ['required', 'string', 'max:150'],
            'AddressItems.*.SubDistrictCode' => ['required', 'string', 'max:5'],
            'AddressItems.*.SubDistrictDesc' => ['required', 'string', 'max:150'],
            'AddressItems.*.ZipCode' => ['required', 'string', 'max:10'],
            'AddressItems.*.Remark' => ['nullable', 'string', 'max:200'],
            'IdentityCardAddressId' => ['required', 'integer', 'min:1'],
            'HouseRegistrationAddressId' => ['required', 'integer', 'min:1'],
            'CurrentAddressId' => ['required', 'integer', 'min:1'],
            'MailingAddressId' => ['required', 'integer', 'min:1'],
            'Phones' => ['required', 'json'],
            'PhoneItems' => ['required', 'array', 'min:1'],
            'PhoneItems.*.PhoneId' => ['required', 'integer', 'min:1', 'distinct'],
            'PhoneItems.*.Phone' => ['required', 'string', 'max:15'],
            'PhoneItems.*.PhoneType' => ['required', Rule::exists('phone_types', 'PhoneTypeCode')],
            'PhoneItems.*.Remark' => ['nullable', 'string', 'max:100'],
            'MobileTelephoneId' => ['required', 'integer', 'min:1'],
            'Email' => ['required', 'email', 'max:50'],
            'EmailRemark' => ['nullable', 'string', 'max:100'],
            'WorkPlace' => ['nullable', 'string', 'max:255'],
            'MonthlyIncomeAmount' => ['nullable', 'numeric', 'min:0'],
            'MonthlyExpenseAmount' => ['nullable', 'numeric', 'min:0'],
            'YearlyBonusAmount' => ['nullable', 'numeric', 'min:0'],
            'Comment' => ['nullable', 'string'],
            ...$this->attachmentRules(),
        ]);

        $addressIds = collect($validated['AddressItems'])->pluck('AddressId')->map(fn ($id) => (int) $id);
        foreach (['IdentityCardAddressId', 'HouseRegistrationAddressId', 'CurrentAddressId', 'MailingAddressId'] as $field) {
            if (! $addressIds->contains((int) $validated[$field])) {
                throw ValidationException::withMessages([$field => __('customerhistory::messages.form.required')]);
            }
        }

        foreach ($validated['AddressItems'] as $index => $address) {
            $locationExists = DB::table('sub_districts')
                ->where('ProvinceCode', $address['ProvinceCode'])
                ->where('DistrictCode', $address['DistrictCode'])
                ->where('SubDistrictCode', $address['SubDistrictCode'])
                ->exists();
            if (! $locationExists) {
                throw ValidationException::withMessages([
                    "AddressItems.$index.SubDistrictCode" => __('customerhistory::messages.form.required'),
                ]);
            }
        }

        $phoneIds = collect($validated['PhoneItems'])->pluck('PhoneId')->map(fn ($id) => (int) $id);
        if (! $phoneIds->contains((int) $validated['MobileTelephoneId'])) {
            throw ValidationException::withMessages(['MobileTelephoneId' => __('customerhistory::messages.form.required')]);
        }

        $workingCondition = DB::table('working_conditions')
            ->where('WorkingConditionId', $validated['WorkingConditionId'])
            ->first(['IsRequireOccupation']);

        if ($workingCondition?->IsRequireOccupation && empty($validated['OccupationCode'])) {
            throw ValidationException::withMessages(['OccupationCode' => __('customerhistory::messages.form.required')]);
        }

        if (! empty($validated['OccupationCode'])) {
            $occupation = DB::table('occupations')
                ->where('OccupationCode', $validated['OccupationCode'])
                ->first(['OccupationDesc', 'IsOtherOccupation', 'Score']);

            if ($occupation?->IsOtherOccupation && empty($validated['OtherOccupationDesc'])) {
                throw ValidationException::withMessages(['OtherOccupationDesc' => __('customerhistory::messages.form.required')]);
            }
        } else {
            $occupation = null;
        }

        if ((string) $validated['IdentityCardTypeCode'] === '1' && ! $this->isValidThaiNationalId($validated['IdentityCardId'])) {
            throw ValidationException::withMessages([
                'IdentityCardId' => __('customerhistory::messages.form.personal.invalid_national_id'),
            ]);
        }

        if ((string) $validated['IdentityCardTypeCode'] === '1'
            && DB::table('customers')
                ->where('IdentityCardTypeCode', 1)
                ->where('IdentityCardId', $validated['IdentityCardId'])
                ->exists()) {
            throw ValidationException::withMessages([
                'IdentityCardId' => __('customerhistory::messages.form.personal.duplicate_national_id'),
            ]);
        }

        $systemUserId = $request->user()->getAuthIdentifier();

        $customerNo = DB::transaction(function () use ($validated, $occupation, $systemUserId): string {
            $customerNo = $this->nextCustomerNo();
            $now = now();
            $legacyAuditUserId = 4;
            $addressTypeCode = $validated['AddressTypeCode'] ?? 1;
            $title = DB::table('titles')->where('TitleCode', $validated['TitleCode'])->first(['TitleDesc']);
            $maritalStatus = DB::table('marital_statuses')->where('MaritalStatusCode', $validated['MaritalStatusCode'])->first(['MaritalStatusName', 'Score']);
            $businessType = ! empty($validated['TypeOfBusinessId'])
                ? DB::table('type_of_businesses')->where('TypeOfBusinessId', $validated['TypeOfBusinessId'])->first(['TypeOfBusinessName', 'BOTCode'])
                : null;
            $addressType = DB::table('address_types')->where('AddressTypeCode', $addressTypeCode)->first(['AddressTypeDesc', 'Score']);
            $age = Carbon::parse($validated['BirthDate'])->age;
            $ageRangeScore = DB::table('age_ranges')
                ->where('FromAge', '<=', $age)
                ->where('ToAge', '>=', $age)
                ->value('Score') ?? 0;
            $monthlyNetIncome = max(0, ((float) ($validated['MonthlyIncomeAmount'] ?? 0))
                - ((float) ($validated['MonthlyExpenseAmount'] ?? 0))
                + (((float) ($validated['YearlyBonusAmount'] ?? 0)) / 12));
            $netIncomeRangeScore = DB::table('net_income_ranges')
                ->where('FromNetIncomeRange', '<=', $monthlyNetIncome)
                ->where('ToNetIncomeRange', '>=', $monthlyNetIncome)
                ->value('Score') ?? 0;
            $currentAddressItem = collect($validated['AddressItems'])->firstWhere('AddressId', (int) $validated['CurrentAddressId']);
            $primaryPhone = collect($validated['PhoneItems'])->firstWhere('PhoneId', (int) $validated['MobileTelephoneId']);
            $currentAddress = implode(' ', array_filter([
                $currentAddressItem['AddressLine1'],
                $currentAddressItem['AddressLine2'] ?? null,
                $currentAddressItem['SubDistrictDesc'],
                $currentAddressItem['DistrictDesc'],
                $currentAddressItem['ProvinceDesc'],
                $currentAddressItem['ZipCode'],
            ]));

            DB::table('customers')->insert([
                'QuickSearchKey' => mb_substr(implode(' ', array_filter([
                    $validated['Nickname'] ?? null,
                    $validated['Firstname'],
                    $validated['Lastname'],
                    $validated['IdentityCardId'],
                    $primaryPhone['Phone'],
                ])), 0, 150),
                'CustomerNo' => $customerNo,
                'CustomerRefNo' => $customerNo,
                'Firstname' => $validated['Firstname'],
                'Lastname' => $validated['Lastname'],
                'Nickname' => $validated['Nickname'] ?? null,
                'TitleCode' => $validated['TitleCode'],
                'TitleDesc' => $title?->TitleDesc,
                'BirthDate' => $validated['BirthDate'],
                'GenderCode' => $validated['GenderCode'],
                'IdentityCardId' => $validated['IdentityCardId'],
                'IdentityCardTypeCode' => $validated['IdentityCardTypeCode'],
                'IdentityCardIssuer' => $validated['IdentityCardIssuer'] ?? null,
                'IdentityCardEffectiveDate' => $validated['IdentityCardEffectiveDate'] ?? null,
                'IdentityCardExpireDate' => $validated['IdentityCardExpireDate'] ?? null,
                'Nationality' => $validated['Nationality'] ?? null,
                'Race' => $validated['Race'] ?? null,
                'MaritalStatusCode' => $validated['MaritalStatusCode'],
                'MaritalStatusDesc' => $maritalStatus?->MaritalStatusName,
                'MaritalStatusScore' => $maritalStatus?->Score,
                'WorkingConditionId' => $validated['WorkingConditionId'],
                'OccupationCode' => $validated['OccupationCode'] ?? null,
                'OccupationDesc' => $occupation?->OccupationDesc,
                'OccupationScore' => $occupation?->Score,
                'OtherOccupationDesc' => $validated['OtherOccupationDesc'] ?? null,
                'TypeOfBusinessId' => $validated['TypeOfBusinessId'] ?? null,
                'TypeOfBusinessName' => $businessType?->TypeOfBusinessName,
                'TypeOfBusinessBotCode' => $businessType?->BOTCode,
                'AddressTypeCode' => $addressTypeCode,
                'AddressTypeDesc' => $addressType?->AddressTypeDesc,
                'AddressTypeScore' => $addressType?->Score,
                'AgeRangeScore' => $ageRangeScore,
                'NetIncomeRangeScore' => $netIncomeRangeScore,
                'IdentityCardAddressId' => $validated['IdentityCardAddressId'],
                'HouseRegistrationAddressId' => $validated['HouseRegistrationAddressId'],
                'CurrentAddressId' => $validated['CurrentAddressId'],
                'MailingAddressId' => $validated['MailingAddressId'],
                'CurrentAddressAsText' => $currentAddress,
                'Mobile' => $primaryPhone['Phone'],
                'MobileTelephoneId' => $validated['MobileTelephoneId'],
                'CustomerAddressLetterId' => 0,
                'CustomerAddressDebtId' => 0,
                'StatementAddressId' => 0,
                'ReceiptAddressId' => 0,
                'HomeTelephoneId' => 0,
                'OfficeTelephoneId' => 0,
                'OtherTelephoneId' => 0,
                'CollectionTelephoneId' => 0,
                'FaxId' => 0,
                'Email' => $validated['Email'],
                'BankCode' => $validated['BankCode'] ?? null,
                'BankBookBranch' => $validated['BankBookBranch'] ?? null,
                'BankBookCode' => $validated['BankBookCode'] ?? null,
                'WorkPlace' => $validated['WorkPlace'] ?? null,
                'MonthlyIncomeAmount' => $validated['MonthlyIncomeAmount'] ?? null,
                'MonthlyExpenseAmount' => $validated['MonthlyExpenseAmount'] ?? null,
                'YearlyBonusAmount' => $validated['YearlyBonusAmount'] ?? null,
                'Score' => 0,
                'CreditLimitAmount' => 0,
                'CreditUsedAmount' => 0,
                'CreditorCreditDay' => 0,
                'CreditorCreditAmount' => 0,
                'IsDebtor' => true,
                'Status' => null,
                'InsertUserId' => $legacyAuditUserId,
                'InsertDate' => $now->toDateString(),
                'sysInsertUserId' => $systemUserId,
                'sysUpdateUserId' => null,
                'sysInsertDateTime' => $now,
                'sysUpdateDateTime' => null,
            ]);

            foreach ($validated['AddressItems'] as $address) {
                DB::table('customer_addresses')->insert([
                    'CustomerNo' => $customerNo,
                    'AddressId' => $address['AddressId'],
                    'AddressLine1' => $address['AddressLine1'],
                    'AddressLine2' => $address['AddressLine2'] ?? null,
                    'ProvinceCode' => $address['ProvinceCode'],
                    'ProvinceDesc' => $address['ProvinceDesc'],
                    'DistrictCode' => $address['DistrictCode'],
                    'DistrictDesc' => $address['DistrictDesc'],
                    'SubDistrictCode' => $address['SubDistrictCode'],
                    'SubDistrictDesc' => $address['SubDistrictDesc'],
                    'ZipCode' => $address['ZipCode'],
                    'AddressTypeCode' => $addressTypeCode,
                    'Remark' => $address['Remark'] ?? null,
                ]);
            }

            foreach ($validated['PhoneItems'] as $phone) {
                DB::table('customer_phones')->insert([
                    'CustomerNo' => $customerNo,
                    'PhoneId' => $phone['PhoneId'],
                    'Remark' => $phone['Remark'] ?? null,
                    'Phone' => $phone['Phone'],
                    'PhoneType' => $phone['PhoneType'],
                ]);
            }

            DB::table('customer_emails')->insert([
                'CustomerNo' => $customerNo,
                'EmailId' => 1,
                'Email' => $validated['Email'],
                'CreateDateTime' => $now,
                'CreateUserId' => $legacyAuditUserId,
                'Remark' => $validated['EmailRemark'] ?? null,
            ]);

            if (! empty($validated['Comment'])) {
                DB::table('customer_remarks')->insert([
                    'CustomerNo' => $customerNo,
                    'RemarkId' => 1,
                    'Comment' => $validated['Comment'],
                    'InsertDateTime' => $now,
                    'InsertUserId' => $legacyAuditUserId,
                ]);
            }

            return $customerNo;
        }, 3);

        $this->storeAttachments($request, $customerNo, $systemUserId);

        return response()->json([
            'message' => __('customerhistory::messages.form.saved_successfully'),
            'customer_no' => $customerNo,
        ], 201);
    }

    public function update(Request $request, string $customerNo): JsonResponse
    {
        if ($request->user()->user_type === 'external' && $request->user()->isManager()) {
            return response()->json([
                'message' => __('customerhistory::messages.manager_read_only'),
            ], 403);
        }

        $customerQuery = DB::table('customers')->where('CustomerNo', $customerNo);
        if ($request->user()->user_type === 'external') {
            $customerQuery->where('sysInsertUserId', $request->user()->getAuthIdentifier());
        }
        $customer = $customerQuery->firstOrFail();
        if ($customer->HmeterTransferStatus === self::TRANSFERRED) {
            return response()->json([
                'message' => __('customerhistory::messages.transfer.update_locked'),
            ], 409);
        }

        foreach (['MonthlyIncomeAmount', 'MonthlyExpenseAmount', 'YearlyBonusAmount'] as $field) {
            if ($request->filled($field)) {
                $request->merge([$field => str_replace(',', '', (string) $request->input($field))]);
            }
        }
        $request->merge([
            'AddressItems' => json_decode((string) $request->input('Addresses', ''), true),
            'PhoneItems' => json_decode((string) $request->input('Phones', ''), true),
        ]);
        $validated = $request->validate($this->customerUpdateRules());

        $addressIds = collect($validated['AddressItems'])->pluck('AddressId')->map(fn ($id) => (int) $id);
        foreach (['IdentityCardAddressId', 'HouseRegistrationAddressId', 'CurrentAddressId', 'MailingAddressId'] as $field) {
            if (! $addressIds->contains((int) $validated[$field])) {
                throw ValidationException::withMessages([$field => __('customerhistory::messages.form.required')]);
            }
        }
        foreach ($validated['AddressItems'] as $index => $address) {
            $locationExists = DB::table('sub_districts')
                ->where('ProvinceCode', $address['ProvinceCode'])
                ->where('DistrictCode', $address['DistrictCode'])
                ->where('SubDistrictCode', $address['SubDistrictCode'])
                ->exists();
            if (! $locationExists) {
                throw ValidationException::withMessages([
                    "AddressItems.$index.SubDistrictCode" => __('customerhistory::messages.form.required'),
                ]);
            }
        }
        $phoneIds = collect($validated['PhoneItems'])->pluck('PhoneId')->map(fn ($id) => (int) $id);
        if (! $phoneIds->contains((int) $validated['MobileTelephoneId'])) {
            throw ValidationException::withMessages(['MobileTelephoneId' => __('customerhistory::messages.form.required')]);
        }

        $workingCondition = DB::table('working_conditions')->where('WorkingConditionId', $validated['WorkingConditionId'])->first(['IsRequireOccupation']);
        if ($workingCondition?->IsRequireOccupation && empty($validated['OccupationCode'])) {
            throw ValidationException::withMessages(['OccupationCode' => __('customerhistory::messages.form.required')]);
        }
        $occupation = ! empty($validated['OccupationCode'])
            ? DB::table('occupations')->where('OccupationCode', $validated['OccupationCode'])->first(['OccupationDesc', 'IsOtherOccupation', 'Score'])
            : null;
        if ($occupation?->IsOtherOccupation && empty($validated['OtherOccupationDesc'])) {
            throw ValidationException::withMessages(['OtherOccupationDesc' => __('customerhistory::messages.form.required')]);
        }
        if ((string) $validated['IdentityCardTypeCode'] === '1' && ! $this->isValidThaiNationalId($validated['IdentityCardId'])) {
            throw ValidationException::withMessages(['IdentityCardId' => __('customerhistory::messages.form.personal.invalid_national_id')]);
        }
        if ((string) $validated['IdentityCardTypeCode'] === '1' && DB::table('customers')
            ->where('IdentityCardTypeCode', 1)->where('IdentityCardId', $validated['IdentityCardId'])
            ->where('CustomerNo', '<>', $customerNo)->exists()) {
            throw ValidationException::withMessages(['IdentityCardId' => __('customerhistory::messages.form.personal.duplicate_national_id')]);
        }

        DB::transaction(function () use ($validated, $occupation, $request, $customerNo): void {
            $now = now();
            $legacyAuditUserId = 4;
            $addressTypeCode = $validated['AddressTypeCode'] ?? 1;
            $title = DB::table('titles')->where('TitleCode', $validated['TitleCode'])->first(['TitleDesc']);
            $maritalStatus = DB::table('marital_statuses')->where('MaritalStatusCode', $validated['MaritalStatusCode'])->first(['MaritalStatusName', 'Score']);
            $businessType = ! empty($validated['TypeOfBusinessId']) ? DB::table('type_of_businesses')->where('TypeOfBusinessId', $validated['TypeOfBusinessId'])->first(['TypeOfBusinessName', 'BOTCode']) : null;
            $addressType = DB::table('address_types')->where('AddressTypeCode', $addressTypeCode)->first(['AddressTypeDesc', 'Score']);
            $age = Carbon::parse($validated['BirthDate'])->age;
            $ageRangeScore = DB::table('age_ranges')->where('FromAge', '<=', $age)->where('ToAge', '>=', $age)->value('Score') ?? 0;
            $monthlyNetIncome = max(0, ((float) ($validated['MonthlyIncomeAmount'] ?? 0)) - ((float) ($validated['MonthlyExpenseAmount'] ?? 0)) + (((float) ($validated['YearlyBonusAmount'] ?? 0)) / 12));
            $netIncomeRangeScore = DB::table('net_income_ranges')->where('FromNetIncomeRange', '<=', $monthlyNetIncome)->where('ToNetIncomeRange', '>=', $monthlyNetIncome)->value('Score') ?? 0;
            $currentAddressItem = collect($validated['AddressItems'])->firstWhere('AddressId', (int) $validated['CurrentAddressId']);
            $primaryPhone = collect($validated['PhoneItems'])->firstWhere('PhoneId', (int) $validated['MobileTelephoneId']);
            $currentAddress = implode(' ', array_filter([$currentAddressItem['AddressLine1'], $currentAddressItem['AddressLine2'] ?? null, $currentAddressItem['SubDistrictDesc'], $currentAddressItem['DistrictDesc'], $currentAddressItem['ProvinceDesc'], $currentAddressItem['ZipCode']]));

            DB::table('customers')->where('CustomerNo', $customerNo)->update([
                'QuickSearchKey' => mb_substr(implode(' ', array_filter([$validated['Nickname'] ?? null, $validated['Firstname'], $validated['Lastname'], $validated['IdentityCardId'], $primaryPhone['Phone']])), 0, 150),
                'Firstname' => $validated['Firstname'], 'Lastname' => $validated['Lastname'], 'Nickname' => $validated['Nickname'] ?? null,
                'TitleCode' => $validated['TitleCode'], 'TitleDesc' => $title?->TitleDesc, 'BirthDate' => $validated['BirthDate'], 'GenderCode' => $validated['GenderCode'],
                'IdentityCardId' => $validated['IdentityCardId'], 'IdentityCardTypeCode' => $validated['IdentityCardTypeCode'], 'IdentityCardIssuer' => $validated['IdentityCardIssuer'] ?? null,
                'IdentityCardEffectiveDate' => $validated['IdentityCardEffectiveDate'] ?? null, 'IdentityCardExpireDate' => $validated['IdentityCardExpireDate'] ?? null,
                'Nationality' => $validated['Nationality'] ?? null, 'Race' => $validated['Race'] ?? null,
                'MaritalStatusCode' => $validated['MaritalStatusCode'], 'MaritalStatusDesc' => $maritalStatus?->MaritalStatusName, 'MaritalStatusScore' => $maritalStatus?->Score ?? 0,
                'WorkingConditionId' => $validated['WorkingConditionId'], 'OccupationCode' => $validated['OccupationCode'] ?? null, 'OccupationDesc' => $occupation?->OccupationDesc,
                'OccupationScore' => $occupation?->Score, 'OtherOccupationDesc' => $validated['OtherOccupationDesc'] ?? null,
                'TypeOfBusinessId' => $validated['TypeOfBusinessId'] ?? null, 'TypeOfBusinessName' => $businessType?->TypeOfBusinessName, 'TypeOfBusinessBotCode' => $businessType?->BOTCode,
                'AddressTypeCode' => $addressTypeCode, 'AddressTypeDesc' => $addressType?->AddressTypeDesc, 'AddressTypeScore' => $addressType?->Score,
                'AgeRangeScore' => $ageRangeScore, 'NetIncomeRangeScore' => $netIncomeRangeScore,
                'IdentityCardAddressId' => $validated['IdentityCardAddressId'], 'HouseRegistrationAddressId' => $validated['HouseRegistrationAddressId'],
                'CurrentAddressId' => $validated['CurrentAddressId'], 'MailingAddressId' => $validated['MailingAddressId'], 'CurrentAddressAsText' => $currentAddress,
                'Mobile' => $primaryPhone['Phone'], 'MobileTelephoneId' => $validated['MobileTelephoneId'], 'Email' => $validated['Email'],
                'BankCode' => $validated['BankCode'] ?? null, 'BankBookBranch' => $validated['BankBookBranch'] ?? null, 'BankBookCode' => $validated['BankBookCode'] ?? null,
                'WorkPlace' => $validated['WorkPlace'] ?? null, 'MonthlyIncomeAmount' => $validated['MonthlyIncomeAmount'] ?? null,
                'MonthlyExpenseAmount' => $validated['MonthlyExpenseAmount'] ?? null, 'YearlyBonusAmount' => $validated['YearlyBonusAmount'] ?? null,
                'UpdateUserId' => $legacyAuditUserId, 'UpdateDate' => $now, 'sysUpdateUserId' => $request->user()->getAuthIdentifier(), 'sysUpdateDateTime' => $now,
            ]);

            DB::table('customer_addresses')->where('CustomerNo', $customerNo)->delete();
            foreach ($validated['AddressItems'] as $address) {
                DB::table('customer_addresses')->insert([
                    'CustomerNo' => $customerNo, 'AddressId' => $address['AddressId'], 'AddressLine1' => $address['AddressLine1'], 'AddressLine2' => $address['AddressLine2'] ?? null,
                    'ProvinceCode' => $address['ProvinceCode'], 'ProvinceDesc' => $address['ProvinceDesc'], 'DistrictCode' => $address['DistrictCode'], 'DistrictDesc' => $address['DistrictDesc'],
                    'SubDistrictCode' => $address['SubDistrictCode'], 'SubDistrictDesc' => $address['SubDistrictDesc'], 'ZipCode' => $address['ZipCode'], 'AddressTypeCode' => $addressTypeCode, 'Remark' => $address['Remark'] ?? null,
                ]);
            }
            DB::table('customer_phones')->where('CustomerNo', $customerNo)->delete();
            foreach ($validated['PhoneItems'] as $phone) {
                DB::table('customer_phones')->insert(['CustomerNo' => $customerNo, 'PhoneId' => $phone['PhoneId'], 'Remark' => $phone['Remark'] ?? null, 'Phone' => $phone['Phone'], 'PhoneType' => $phone['PhoneType']]);
            }
            DB::table('customer_emails')->where('CustomerNo', $customerNo)->delete();
            DB::table('customer_emails')->insert(['CustomerNo' => $customerNo, 'EmailId' => 1, 'Email' => $validated['Email'], 'CreateDateTime' => $now, 'CreateUserId' => $legacyAuditUserId, 'Remark' => $validated['EmailRemark'] ?? null]);
            DB::table('customer_remarks')->where('CustomerNo', $customerNo)->delete();
            if (! empty($validated['Comment'])) {
                DB::table('customer_remarks')->insert(['CustomerNo' => $customerNo, 'RemarkId' => 1, 'Comment' => $validated['Comment'], 'InsertDateTime' => $now, 'InsertUserId' => $legacyAuditUserId]);
            }
        }, 3);

        $this->removeAttachments($request, $customerNo);
        $this->storeAttachments($request, $customerNo, $request->user()->getAuthIdentifier());

        return response()->json(['message' => __('customerhistory::messages.form.updated_successfully'), 'customer_no' => $customerNo]);
    }

    private function customerUpdateRules(): array
    {
        return [
            'TitleCode' => ['required', Rule::exists('titles', 'TitleCode')], 'Firstname' => ['required', 'string', 'max:100'], 'Lastname' => ['required', 'string', 'max:50'],
            'Nickname' => ['nullable', 'string', 'max:10'], 'GenderCode' => ['required', Rule::exists('genders', 'GenderId')], 'BirthDate' => ['required', 'date'],
            'IdentityCardTypeCode' => ['required', Rule::exists('identity_card_types', 'IdentityCardTypeCode')], 'IdentityCardId' => ['required', 'string', 'max:20'],
            'IdentityCardIssuer' => ['nullable', 'string', 'max:100'], 'IdentityCardEffectiveDate' => ['nullable', 'date'], 'IdentityCardExpireDate' => ['nullable', 'date', 'after_or_equal:IdentityCardEffectiveDate'],
            'Nationality' => ['nullable', 'string', 'max:50'], 'Race' => ['nullable', 'string', 'max:50'], 'MaritalStatusCode' => ['required', Rule::exists('marital_statuses', 'MaritalStatusCode')],
            'WorkingConditionId' => ['required', Rule::exists('working_conditions', 'WorkingConditionId')], 'OccupationCode' => ['nullable', Rule::exists('occupations', 'OccupationCode')],
            'TypeOfBusinessId' => ['nullable', Rule::exists('type_of_businesses', 'TypeOfBusinessId')], 'OtherOccupationDesc' => ['nullable', 'string', 'max:255'],
            'AddressTypeCode' => ['nullable', Rule::exists('address_types', 'AddressTypeCode')], 'BankCode' => ['nullable', Rule::exists('banks', 'BankCode')], 'BankBookBranch' => ['nullable', 'string', 'max:50'], 'BankBookCode' => ['nullable', 'string', 'max:20'],
            'Addresses' => ['required', 'json'], 'AddressItems' => ['required', 'array', 'min:1'], 'AddressItems.*.AddressId' => ['required', 'integer', 'min:1', 'distinct'],
            'AddressItems.*.AddressLine1' => ['required', 'string', 'max:100'], 'AddressItems.*.AddressLine2' => ['nullable', 'string', 'max:100'], 'AddressItems.*.ProvinceCode' => ['required', Rule::exists('provinces', 'ProvinceCode')],
            'AddressItems.*.ProvinceDesc' => ['required', 'string', 'max:150'], 'AddressItems.*.DistrictCode' => ['required', 'string', 'max:5'], 'AddressItems.*.DistrictDesc' => ['required', 'string', 'max:150'],
            'AddressItems.*.SubDistrictCode' => ['required', 'string', 'max:5'], 'AddressItems.*.SubDistrictDesc' => ['required', 'string', 'max:150'], 'AddressItems.*.ZipCode' => ['required', 'string', 'max:10'], 'AddressItems.*.Remark' => ['nullable', 'string', 'max:200'],
            'IdentityCardAddressId' => ['required', 'integer', 'min:1'], 'HouseRegistrationAddressId' => ['required', 'integer', 'min:1'], 'CurrentAddressId' => ['required', 'integer', 'min:1'], 'MailingAddressId' => ['required', 'integer', 'min:1'],
            'Phones' => ['required', 'json'], 'PhoneItems' => ['required', 'array', 'min:1'], 'PhoneItems.*.PhoneId' => ['required', 'integer', 'min:1', 'distinct'], 'PhoneItems.*.Phone' => ['required', 'string', 'max:15'],
            'PhoneItems.*.PhoneType' => ['required', Rule::exists('phone_types', 'PhoneTypeCode')], 'PhoneItems.*.Remark' => ['nullable', 'string', 'max:100'], 'MobileTelephoneId' => ['required', 'integer', 'min:1'],
            'Email' => ['required', 'email', 'max:50'], 'EmailRemark' => ['nullable', 'string', 'max:100'], 'WorkPlace' => ['nullable', 'string', 'max:255'],
            'MonthlyIncomeAmount' => ['nullable', 'numeric', 'min:0'], 'MonthlyExpenseAmount' => ['nullable', 'numeric', 'min:0'], 'YearlyBonusAmount' => ['nullable', 'numeric', 'min:0'], 'Comment' => ['nullable', 'string'],
            ...$this->attachmentRules(),
        ];
    }

    private function attachmentRules(): array
    {
        $rules = [
            'NewAttachments' => ['nullable', 'array'],
            'NewAttachments.*.DocumentName' => ['required', 'string', 'max:255'],
            'NewAttachments.*.DocumentTypeId' => [
                'required',
                Rule::exists('document_types', 'id')->where('Active', true),
            ],
            'NewAttachments.*.File' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'RemoveAttachmentIds' => ['nullable', 'array'],
            'RemoveAttachmentIds.*' => ['integer'],
        ];

        return $rules;
    }

    private function storeAttachments(Request $request, string $customerNo, int|string|null $userId): void
    {
        foreach ($request->input('NewAttachments', []) as $index => $attachment) {
            $file = $request->file("NewAttachments.$index.File");
            $path = $file->store("customer-attachments/$customerNo", 'local');

            if (! $path) {
                throw new \RuntimeException('Unable to store customer attachment.');
            }

            DB::table('customer_attachments')->insert([
                'CustomerNo' => $customerNo,
                'DocumentName' => $attachment['DocumentName'],
                'DocumentTypeId' => $attachment['DocumentTypeId'],
                'OriginalName' => $file->getClientOriginalName(),
                'FilePath' => $path,
                'MimeType' => $file->getMimeType(),
                'FileSize' => $file->getSize(),
                'UploadedBy' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function removeAttachments(Request $request, string $customerNo): void
    {
        $ids = collect($request->input('RemoveAttachmentIds', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return;
        }

        $attachments = DB::table('customer_attachments')
            ->where('CustomerNo', $customerNo)
            ->whereIn('id', $ids)
            ->get();

        DB::table('customer_attachments')->whereIn('id', $attachments->pluck('id'))->delete();
        Storage::disk('local')->delete($attachments->pluck('FilePath')->all());
    }

    private function nextCustomerNo(): string
    {
        $customerNumberDate = now();
        $prefix = '00CU'.$customerNumberDate->format('ymd');
        $latestCustomerNo = DB::table('customers')
            ->where('CustomerNo', 'like', $prefix.'%')
            ->orderByDesc('CustomerNo')
            ->lockForUpdate()
            ->value('CustomerNo');
        $sequence = $latestCustomerNo ? ((int) substr($latestCustomerNo, 10)) + 1 : 1;

        if ($sequence > 999999) {
            throw new \RuntimeException('Daily customer number range is exhausted.');
        }

        return $prefix.str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }

    private function utcDateTime(mixed $value): ?string
    {
        return $value ? Carbon::parse((string) $value, 'UTC')->toISOString() : null;
    }

    private function restrictCustomerReadAccess(Builder $query, User $user, string $table = 'customers'): void
    {
        if ($user->user_type === 'internal') {
            return;
        }

        if ($user->isManager()) {
            $query->whereIn("{$table}.sysInsertUserId", DB::table('users')
                ->where('user_type', 'external')
                ->select('id'));

            return;
        }

        $query->where("{$table}.sysInsertUserId", $user->getAuthIdentifier());
    }

    private function isValidThaiNationalId(string $identity): bool
    {
        if (! preg_match('/^\d{13}$/', $identity) || count(array_unique(str_split($identity))) === 1) {
            return false;
        }

        $sum = 0;
        for ($index = 0; $index < 12; $index++) {
            $sum += ((int) $identity[$index]) * (13 - $index);
        }

        return ((11 - ($sum % 11)) % 10) === (int) $identity[12];
    }
}
