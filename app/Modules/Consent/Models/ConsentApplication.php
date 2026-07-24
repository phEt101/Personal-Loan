<?php

namespace App\Modules\Consent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class ConsentApplication extends Model
{
    use SoftDeletes;

    protected $table = 'consent_requests';

    protected $fillable = [
        'encrypted_id',
        'app_date',
        'app_no',
        'officer_name',
        'officer_phone',
        'loan_product_id',
        'officer_group_id',
        'document_delivery',
        'status',
        'loan_status',
        'signed',
        'signed_at',
        'signature_data',
    ];

    protected $casts = [
        'app_date' => 'date',
        'signed' => 'boolean',
        'signed_at' => 'datetime',
        'loan_product_id' => 'integer',
        'officer_group_id' => 'integer',
    ];

     protected static function booted(): void {
        static::created(function (self $model) {
            if ($model->encrypted_id) {
                return;
            }

            $model->encrypted_id = static::makeEncryptedId((string) $model->getKey());
            $model->saveQuietly();
        });
    }

    public function getRouteKeyName(): string {
        return 'encrypted_id';
    }

    public static function makeEncryptedId(string $id): string {
        $encrypted = Crypt::encryptString($id);

        return rtrim(strtr($encrypted, '+/', '-_'), '=');
    }

    public function loanProduct()
    {
        return $this->belongsTo(LoanProduct::class, 'loan_product_id');
    }

    public function officerGroup()
    {
        return $this->belongsTo(OfficerGroup::class, 'officer_group_id');
    }

    public function applicants(): HasMany
    {
        return $this->hasMany(ConsentApplicant::class, 'application_id');
    }

    public function contact(): HasOneThrough
    {
        return $this->hasOneThrough(
            ConsentContact::class,
            ConsentApplicant::class,
            'application_id', // foreign key on applicants
            'applicant_id',    // foreign key on contacts
            'id',
            'id'
        );
    }

    public function addresses(): HasManyThrough
    {
        return $this->hasManyThrough(
            ConsentAddress::class,
            ConsentApplicant::class,
            'application_id', // foreign key on applicants
            'applicant_id',    // foreign key on addresses
            'id',
            'id'
        );
    }

    public function incomeDocuments(): HasMany
    {
        return $this->hasMany(ConsentDocumentFile::class, 'application_id');
    }

    public function homeAddress(): HasOneThrough
    {
        return $this->hasOneThrough(
            ConsentAddress::class,
            ConsentApplicant::class,
            'application_id',
            'applicant_id',
            'id',
            'id'
        )->where('consent_addresses.kind', 'home');
    }

    public function workAddress(): HasOneThrough
    {
        return $this->hasOneThrough(
            ConsentAddress::class,
            ConsentApplicant::class,
            'application_id',
            'applicant_id',
            'id',
            'id'
        )->where('consent_addresses.kind', 'work');
    }

    public function referenceAddress(): HasOneThrough
    {
        return $this->hasOneThrough(
            ConsentAddress::class,
            ConsentApplicant::class,
            'application_id',
            'applicant_id',
            'id',
            'id'
        )->where('consent_addresses.kind', 'reference');
    }

    public function documentAddress(): HasOneThrough
    {
        return $this->hasOneThrough(
            ConsentAddress::class,
            ConsentApplicant::class,
            'application_id',
            'applicant_id',
            'id',
            'id'
        )->where('consent_addresses.kind', 'document');
    }

    public function employment(): HasOne
    {
        return $this->hasOne(ConsentEmployment::class, 'application_id');
    }

    public function previousEmployment(): HasOne
    {
        return $this->hasOne(ConsentPreviousEmployment::class, 'application_id');
    }

    public function reference(): HasOne
    {
        return $this->hasOne(ConsentReference::class, 'application_id');
    }

    public function loanRequest(): HasOne
    {
        return $this->hasOne(ConsentLoanRequest::class, 'application_id');
    }

    public function disbursementAccount(): HasOne
    {
        return $this->hasOne(ConsentDisbursementAccount::class, 'application_id');
    }

    public function loanApproval(): HasOne
    {
        return $this->hasOne(ConsentLoanApproval::class, 'application_id');
    }

    public function loanSchedules(): HasMany
    {
        return $this->hasMany(ConsentLoanSchedule::class, 'application_id');
    }
}
