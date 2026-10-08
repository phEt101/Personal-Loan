<?php

namespace App\Modules\CustomerHistory\Models;

use App\Modules\Settings\Models\User;
use App\Modules\WorkDelegation\Models\WorkDelegation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $table = 'customers';

    protected $fillable = [
        'QuickSearchKey',
        'CustomerNo',
        'CustomerTypeCode',
        'Firstname',
        'Lastname',
        'Nickname',
        'TitleCode',
        'BirthDate',
        'IdentityCardId',
        'IdentityCardTypeCode',
        'IdentityCardIssuer',
        'Nationality',
        'MaritalStatusCode',
        'OccupationCode',
        'Mobile',
        'IdentityCardAddressId',
        'HouseRegistrationAddressId',
        'CurrentAddressId',
        'MailingAddressId',
        'WorkPlace',
        'MonthlyIncomeAmount',
        'MonthlyExpenseAmount',
        'YearlyBonusAmount',
        'Score',
        'CreditLimitAmount',
        'CreditUsedAmount',
        'OrganizationBranchTypeCode',
        'OrganizationBranchNo',
        'Email',
        'LineUserId',
        'ContactPerson',
        'InsertUserId',
        'InsertDate',
        'UpdateUserId',
        'UpdateDate',
        'DeleteUserId',
        'DeleteDate',
        'CustomerAvatar',
        'GenderCode',
        'CustomerGradeCode',
        'CurrentAddressAsText',
        'AddressTypeCode',
        'AgeRangeScore',
        'NetIncomeRangeScore',
        'AddressTypeScore',
        'OccupationScore',
        'MaritalStatusScore',
        'IsCreditor',
        'IsDebtor',
        'CreditorCreditDay',
        'CreditorCreditAmount',
        'IsDealer',
        'IsInsurer',
        'IsOutsource',
        'IsLawyer',
        'BankCode',
        'BankBookCode',
        'BankBookBranch',
        'IsSalesRepresentative',
        'TitleDesc',
        'RegistrationNo',
        'Status',
        'IsAuction',
        'MaritalStatusDesc',
        'OccupationDesc',
        'AddressTypeDesc',
        'CustomerAddressLetterId',
        'CustomerAddressDebtId',
        'StatementAddressId',
        'ReceiptAddressId',
        'HomeTelephoneId',
        'OfficeTelephoneId',
        'MobileTelephoneId',
        'OtherTelephoneId',
        'CollectionTelephoneId',
        'FaxId',
        'Race',
        'TypeOfBusinessId',
        'TypeOfBusinessName',
        'TypeOfBusinessBotCode',
        'OtherOccupationDesc',
        'WorkingConditionId',
        'AccountApDeposit',
        'AccountAp',
        'AccountOtherSuspend',
        'CustomerRegistrationTypeCode',
        'RegistrationTypeValue',
        'OrganizationNameNcb',
        'IsOutsourceLegal',
        'IdentityCardEffectiveDate',
        'IdentityCardExpireDate',
        'IsSupplier',
        'IsFinance',
        'OrganizationRegisteredDate',
        'OrganizationRegisteredCapital',
        'HeadquartersBranchNo',
        'HeadquartersBranchDesc',
        'CustomerRefNo',
        'sysInsertUserId',
        'sysUpdateUserId',
        'sysInsertDateTime',
        'sysUpdateDateTime',
        'HmeterTransferStatus',
        'HmeterTransferredAt',
        'HmeterTransferredBy',
        'HmeterWorkDelegationId',
        'AttachmentPurgeAfter',
        'AttachmentsPurgedAt',
    ];

    public $timestamps = false;

    public function getRouteKeyName(): string
    {
        return 'CustomerNo';
    }

    protected function casts(): array
    {
        return [
            'BirthDate' => 'date',
            'sysInsertDateTime' => 'datetime',
            'sysUpdateDateTime' => 'datetime',
            'HmeterTransferredAt' => 'datetime',
            'AttachmentPurgeAfter' => 'datetime',
            'AttachmentsPurgedAt' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sysInsertUserId');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sysUpdateUserId');
    }

    public function transferConfirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'HmeterTransferredBy');
    }

    public function workDelegation(): BelongsTo
    {
        return $this->belongsTo(WorkDelegation::class, 'HmeterWorkDelegationId');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class, 'CustomerNo', 'CustomerNo');
    }

    public function phones(): HasMany
    {
        return $this->hasMany(CustomerPhone::class, 'CustomerNo', 'CustomerNo');
    }

    public function emails(): HasMany
    {
        return $this->hasMany(CustomerEmail::class, 'CustomerNo', 'CustomerNo');
    }

    public function remarks(): HasMany
    {
        return $this->hasMany(CustomerRemark::class, 'CustomerNo', 'CustomerNo');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(CustomerAttachment::class, 'CustomerNo', 'CustomerNo');
    }
}
