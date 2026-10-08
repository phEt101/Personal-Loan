<?php

namespace App\Modules\CustomerHistory\Http\Controllers;

use App\Modules\CustomerHistory\Models\Customer;
use App\Modules\CustomerHistory\Models\CustomerAddress;
use App\Modules\CustomerHistory\Models\CustomerAttachment;
use App\Modules\CustomerHistory\Models\CustomerEmail;
use App\Modules\CustomerHistory\Models\CustomerPhone;
use App\Modules\CustomerHistory\Models\CustomerRemark;
use App\Modules\Settings\Models\User;
use App\Modules\Settings\Services\CustomerAccessService;
use App\Modules\WorkDelegation\Models\WorkDelegation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CustomerController extends Controller
{
    private const TRANSFER_PENDING = 'pending';

    private const TRANSFERRED = 'transferred';

    private const ATTACHMENT_RETENTION_DAYS = 30;

    public function __construct(private readonly CustomerAccessService $customerAccess) {}

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

        $query = Customer::query()
            ->where('IdentityCardTypeCode', 1)
            ->where('IdentityCardId', $validated['identity_card_id']);

        if ($request->filled('customer_no')) {
            $query->where('CustomerNo', '<>', (string) $request->query('customer_no'));
        }

        return response()->json(['exists' => $query->exists()]);
    }

    public function previewAttachment(Request $request, int $attachment)
    {
        $record = CustomerAttachment::query()->findOrFail($attachment);
        $customerQuery = Customer::query()->where('CustomerNo', $record->CustomerNo);

        $this->restrictCustomerReadAccess($customerQuery, $request->user());

        $customerQuery->firstOrFail();
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');
        abort_unless($disk->exists($record->FilePath), 404);

        return $disk->response($record->FilePath, $record->OriginalName, [], 'inline');
    }

    public function downloadAllAttachments(Request $request, string $customerNo)
    {
        $customerQuery = Customer::query()->where('CustomerNo', $customerNo);

        $this->restrictCustomerReadAccess($customerQuery, $request->user());

        $customerQuery->firstOrFail();
        $attachments = CustomerAttachment::query()
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
        $customer = Customer::query()
            ->from('customers as customer')
            ->leftJoin('users as creator', 'creator.id', '=', 'customer.sysInsertUserId')
            ->where('customer.CustomerNo', $customerNo)
            ->where('customer.HmeterTransferStatus', self::TRANSFER_PENDING)
            ->select('customer.CustomerNo', 'creator.responsibility_group_id as CreatorGroupId');
        $this->customerAccess->applyReadScope($customer, $request->user(), 'customer');
        $customer = $customer->first();

        if (! $customer) {
            $accessibleCustomer = Customer::query()->where('CustomerNo', $customerNo);
            $this->customerAccess->applyReadScope($accessibleCustomer, $request->user());
            abort_unless($accessibleCustomer->exists(), 404);

            return response()->json([
                'message' => __('customerhistory::messages.transfer.already_transferred'),
            ], 409);
        }

        $delegationId = $this->activeDelegationId(
            $request->user(),
            $customer->CreatorGroupId,
            $now
        );
        $customerQuery = Customer::query()
            ->where('CustomerNo', $customerNo)
            ->where('HmeterTransferStatus', self::TRANSFER_PENDING);
        $this->customerAccess->applyReadScope($customerQuery, $request->user());

        $updated = $customerQuery->update([
            'HmeterTransferStatus' => self::TRANSFERRED,
            'HmeterTransferredAt' => $now,
            'HmeterTransferredBy' => $request->user()->getAuthIdentifier(),
            'HmeterWorkDelegationId' => $delegationId,
            'AttachmentPurgeAfter' => $now->copy()->addDays(self::ATTACHMENT_RETENTION_DAYS),
            'AttachmentsPurgedAt' => null,
        ]);

        if ($updated === 0) {
            $accessibleCustomer = Customer::query()->where('CustomerNo', $customerNo);
            $this->customerAccess->applyReadScope($accessibleCustomer, $request->user());
            abort_unless($accessibleCustomer->exists(), 404);

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

    private function activeDelegationId(User $user, ?int $groupId, $at): ?int
    {
        if ($groupId === null || $user->isAdmin() || (int) $user->responsibility_group_id === $groupId) {
            return null;
        }

        $delegationId = WorkDelegation::query()
            ->where('responsibility_group_id', $groupId)
            ->where('delegate_user_id', $user->getAuthIdentifier())
            ->whereNull('cancelled_at')
            ->where('starts_at', '<=', $at)
            ->where('ends_at', '>=', $at)
            ->value('id');

        return $delegationId === null ? null : (int) $delegationId;
    }

    public function show(Request $request, string $customerNo): JsonResponse
    {
        $user = $request->user();
        $customerQuery = Customer::query()
            ->from('customers as customer')
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

        $email = $customer->emails()->orderBy('EmailId')->first();
        $remark = $customer->remarks()->orderBy('RemarkId')->first();

        return response()->json([
            'customer' => $customer,
            'addresses' => $customer->addresses()->orderBy('AddressId')->get(),
            'phones' => $customer->phones()->orderBy('PhoneId')->get(),
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
            'attachments' => CustomerAttachment::query()
                ->from('customer_attachments as attachment')
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
                    'preview_url' => route('customer-history.attachments.preview', $attachment->id),
                ]),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if ($this->isReadOnlyManager($request->user())) {
            return $this->managerReadOnlyResponse();
        }

        $this->prepareCustomerRequest($request);
        $validated = $request->validate($this->customerRules());
        $occupation = $this->validateCustomerData($validated);
        $systemUserId = $request->user()->getAuthIdentifier();
        $customerNo = DB::transaction(
            fn (): string => $this->createCustomer($validated, $occupation, $systemUserId),
            3
        );

        $this->storeAttachments($request, $customerNo, $systemUserId);

        return response()->json([
            'message' => __('customerhistory::messages.form.saved_successfully'),
            'customer_no' => $customerNo,
        ], 201);
    }

    public function update(Request $request, string $customerNo): JsonResponse
    {
        if ($this->isReadOnlyManager($request->user())) {
            return $this->managerReadOnlyResponse();
        }

        $customerQuery = Customer::query()->where('CustomerNo', $customerNo);
        $this->customerAccess->applyReadScope($customerQuery, $request->user());

        $customer = $customerQuery->firstOrFail();
        if ($customer->HmeterTransferStatus === self::TRANSFERRED) {
            return response()->json([
                'message' => __('customerhistory::messages.transfer.update_locked'),
            ], 409);
        }

        $this->prepareCustomerRequest($request);
        $validated = $request->validate($this->customerRules());
        $occupation = $this->validateCustomerData($validated, $customerNo);
        $systemUserId = $request->user()->getAuthIdentifier();

        DB::transaction(
            fn () => $this->updateCustomer($customerNo, $validated, $occupation, $systemUserId),
            3
        );

        $this->removeAttachments($request, $customerNo);
        $this->storeAttachments($request, $customerNo, $systemUserId);

        return response()->json([
            'message' => __('customerhistory::messages.form.updated_successfully'),
            'customer_no' => $customerNo,
        ]);
    }

    private function isReadOnlyManager(User $user): bool
    {
        return $user->user_type === 'external' && $user->isManager();
    }

    private function managerReadOnlyResponse(): JsonResponse
    {
        return response()->json([
            'message' => __('customerhistory::messages.manager_read_only'),
        ], 403);
    }

    private function prepareCustomerRequest(Request $request): void
    {
        foreach (['MonthlyIncomeAmount', 'MonthlyExpenseAmount', 'YearlyBonusAmount'] as $field) {
            if ($request->filled($field)) {
                $request->merge([$field => str_replace(',', '', (string) $request->input($field))]);
            }
        }

        $request->merge([
            'AddressItems' => json_decode((string) $request->input('Addresses', ''), true),
            'PhoneItems' => json_decode((string) $request->input('Phones', ''), true),
        ]);
    }

    private function validateCustomerData(array $validated, ?string $currentCustomerNo = null): ?object
    {
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
            throw ValidationException::withMessages([
                'MobileTelephoneId' => __('customerhistory::messages.form.required'),
            ]);
        }

        $workingCondition = DB::table('working_conditions')
            ->where('WorkingConditionId', $validated['WorkingConditionId'])
            ->first(['IsRequireOccupation']);
        if ($workingCondition?->IsRequireOccupation && empty($validated['OccupationCode'])) {
            throw ValidationException::withMessages([
                'OccupationCode' => __('customerhistory::messages.form.required'),
            ]);
        }

        $occupation = empty($validated['OccupationCode'])
            ? null
            : DB::table('occupations')
                ->where('OccupationCode', $validated['OccupationCode'])
                ->first(['OccupationDesc', 'IsOtherOccupation', 'Score']);
        if ($occupation?->IsOtherOccupation && empty($validated['OtherOccupationDesc'])) {
            throw ValidationException::withMessages([
                'OtherOccupationDesc' => __('customerhistory::messages.form.required'),
            ]);
        }

        $this->validateCustomerIdentity($validated, $currentCustomerNo);

        return $occupation;
    }

    private function validateCustomerIdentity(array $validated, ?string $currentCustomerNo): void
    {
        if ((string) $validated['IdentityCardTypeCode'] !== '1') {
            return;
        }

        if (! $this->isValidThaiNationalId($validated['IdentityCardId'])) {
            throw ValidationException::withMessages([
                'IdentityCardId' => __('customerhistory::messages.form.personal.invalid_national_id'),
            ]);
        }

        $duplicateIdentityQuery = Customer::query()
            ->where('IdentityCardTypeCode', 1)
            ->where('IdentityCardId', $validated['IdentityCardId']);

        if ($currentCustomerNo !== null) {
            $duplicateIdentityQuery->where('CustomerNo', '<>', $currentCustomerNo);
        }

        if ($duplicateIdentityQuery->exists()) {
            throw ValidationException::withMessages([
                'IdentityCardId' => __('customerhistory::messages.form.personal.duplicate_national_id'),
            ]);
        }
    }

    private function createCustomer(array $validated, ?object $occupation, int|string|null $systemUserId): string
    {
        $customerNo = $this->nextCustomerNo();
        $now = now();
        $values = $this->customerValues($validated, $occupation);

        Customer::query()->create([
            ...$values,
            'CustomerNo' => $customerNo,
            'CustomerRefNo' => $customerNo,
            'CustomerAddressLetterId' => 0,
            'CustomerAddressDebtId' => 0,
            'StatementAddressId' => 0,
            'ReceiptAddressId' => 0,
            'HomeTelephoneId' => 0,
            'OfficeTelephoneId' => 0,
            'OtherTelephoneId' => 0,
            'CollectionTelephoneId' => 0,
            'FaxId' => 0,
            'Score' => 0,
            'CreditLimitAmount' => 0,
            'CreditUsedAmount' => 0,
            'CreditorCreditDay' => 0,
            'CreditorCreditAmount' => 0,
            'IsDebtor' => true,
            'Status' => null,
            'InsertUserId' => 4,
            'InsertDate' => $now->copy()->timezone(config('app.local_timezone'))->toDateString(),
            'sysInsertUserId' => $systemUserId,
            'sysUpdateUserId' => null,
            'sysInsertDateTime' => $now,
            'sysUpdateDateTime' => null,
        ]);

        $this->replaceCustomerRelations($customerNo, $validated, $values['AddressTypeCode'], $now);

        return $customerNo;
    }

    private function updateCustomer(
        string $customerNo,
        array $validated,
        ?object $occupation,
        int|string|null $systemUserId
    ): void {
        $now = now();
        $values = $this->customerValues($validated, $occupation);

        Customer::query()->where('CustomerNo', $customerNo)->update([
            ...$values,
            'UpdateUserId' => 4,
            'UpdateDate' => $now,
            'sysUpdateUserId' => $systemUserId,
            'sysUpdateDateTime' => $now,
        ]);

        $this->replaceCustomerRelations($customerNo, $validated, $values['AddressTypeCode'], $now);
    }

    private function customerValues(array $validated, ?object $occupation): array
    {
        $addressTypeCode = $validated['AddressTypeCode'] ?? 1;
        $title = DB::table('titles')->where('TitleCode', $validated['TitleCode'])->first(['TitleDesc']);
        $maritalStatus = DB::table('marital_statuses')
            ->where('MaritalStatusCode', $validated['MaritalStatusCode'])
            ->first(['MaritalStatusName', 'Score']);
        $businessType = empty($validated['TypeOfBusinessId'])
            ? null
            : DB::table('type_of_businesses')
                ->where('TypeOfBusinessId', $validated['TypeOfBusinessId'])
                ->first(['TypeOfBusinessName', 'BOTCode']);
        $addressType = DB::table('address_types')
            ->where('AddressTypeCode', $addressTypeCode)
            ->first(['AddressTypeDesc', 'Score']);
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
        $currentAddress = collect($validated['AddressItems'])
            ->firstWhere('AddressId', (int) $validated['CurrentAddressId']);
        $primaryPhone = collect($validated['PhoneItems'])
            ->firstWhere('PhoneId', (int) $validated['MobileTelephoneId']);
        $currentAddressText = implode(' ', array_filter([
            $currentAddress['AddressLine1'],
            $currentAddress['AddressLine2'] ?? null,
            $currentAddress['SubDistrictDesc'],
            $currentAddress['DistrictDesc'],
            $currentAddress['ProvinceDesc'],
            $currentAddress['ZipCode'],
        ]));

        return [
            'QuickSearchKey' => mb_substr(implode(' ', array_filter([
                $validated['Nickname'] ?? null,
                $validated['Firstname'],
                $validated['Lastname'],
                $validated['IdentityCardId'],
                $primaryPhone['Phone'],
            ])), 0, 150),
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
            'CurrentAddressAsText' => $currentAddressText,
            'Mobile' => $primaryPhone['Phone'],
            'MobileTelephoneId' => $validated['MobileTelephoneId'],
            'Email' => $validated['Email'],
            'BankCode' => $validated['BankCode'] ?? null,
            'BankBookBranch' => $validated['BankBookBranch'] ?? null,
            'BankBookCode' => $validated['BankBookCode'] ?? null,
            'WorkPlace' => $validated['WorkPlace'] ?? null,
            'MonthlyIncomeAmount' => $validated['MonthlyIncomeAmount'] ?? null,
            'MonthlyExpenseAmount' => $validated['MonthlyExpenseAmount'] ?? null,
            'YearlyBonusAmount' => $validated['YearlyBonusAmount'] ?? null,
        ];
    }

    private function replaceCustomerRelations(
        string $customerNo,
        array $validated,
        int|string $addressTypeCode,
        Carbon $now
    ): void {
        CustomerAddress::query()->where('CustomerNo', $customerNo)->delete();
        CustomerAddress::query()->insert(
            collect($validated['AddressItems'])->map(fn (array $address) => [
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
            ])->all()
        );

        CustomerPhone::query()->where('CustomerNo', $customerNo)->delete();
        CustomerPhone::query()->insert(
            collect($validated['PhoneItems'])->map(fn (array $phone) => [
                'CustomerNo' => $customerNo,
                'PhoneId' => $phone['PhoneId'],
                'Remark' => $phone['Remark'] ?? null,
                'Phone' => $phone['Phone'],
                'PhoneType' => $phone['PhoneType'],
            ])->all()
        );

        CustomerEmail::query()->where('CustomerNo', $customerNo)->delete();
        CustomerEmail::query()->create([
            'CustomerNo' => $customerNo,
            'EmailId' => 1,
            'Email' => $validated['Email'],
            'CreateDateTime' => $now,
            'CreateUserId' => 4,
            'Remark' => $validated['EmailRemark'] ?? null,
        ]);

        CustomerRemark::query()->where('CustomerNo', $customerNo)->delete();
        if (! empty($validated['Comment'])) {
            CustomerRemark::query()->create([
                'CustomerNo' => $customerNo,
                'RemarkId' => 1,
                'Comment' => $validated['Comment'],
                'InsertDateTime' => $now,
                'InsertUserId' => 4,
            ]);
        }
    }

    private function customerRules(): array
    {
        return [
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
        ];
    }

    private function attachmentRules(): array
    {
        return [
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
    }

    private function storeAttachments(Request $request, string $customerNo, int|string|null $userId): void
    {
        foreach ($request->input('NewAttachments', []) as $index => $attachment) {
            $file = $request->file("NewAttachments.$index.File");
            $path = $file->store("customer-attachments/$customerNo", 'local');

            abort_if(! $path, 500, 'Unable to store customer attachment.');

            CustomerAttachment::query()->create([
                'CustomerNo' => $customerNo,
                'DocumentName' => $attachment['DocumentName'],
                'DocumentTypeId' => $attachment['DocumentTypeId'],
                'OriginalName' => $file->getClientOriginalName(),
                'FilePath' => $path,
                'MimeType' => $file->getMimeType(),
                'FileSize' => $file->getSize(),
                'UploadedBy' => $userId,
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

        $attachments = CustomerAttachment::query()
            ->where('CustomerNo', $customerNo)
            ->whereIn('id', $ids)
            ->get();

        CustomerAttachment::query()->whereIn('id', $attachments->pluck('id'))->delete();
        Storage::disk('local')->delete($attachments->pluck('FilePath')->all());
    }

    private function nextCustomerNo(): string
    {
        $customerNumberDate = now(config('app.local_timezone'));
        $prefix = '00CU'.$customerNumberDate->format('ymd');
        $latestCustomerNo = Customer::query()
            ->where('CustomerNo', 'like', $prefix.'%')
            ->orderByDesc('CustomerNo')
            ->lockForUpdate()
            ->value('CustomerNo');
        $sequence = $latestCustomerNo ? ((int) substr($latestCustomerNo, 10)) + 1 : 1;

        abort_if($sequence > 999999, 409, 'Daily customer number range is exhausted.');

        return $prefix.str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }

    private function utcDateTime(mixed $value): ?string
    {
        return $value ? Carbon::parse((string) $value, 'UTC')->toISOString() : null;
    }

    private function restrictCustomerReadAccess(Builder $query, User $user, string $table = 'customers'): void
    {
        $this->customerAccess->applyReadScope($query, $user, $table);
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
