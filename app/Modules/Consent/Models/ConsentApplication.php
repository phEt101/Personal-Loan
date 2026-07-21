<?php

namespace App\Modules\Consent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        'officer_group',
        'product_type',
        'document_delivery',
        'status',
        'signed',
        'signed_at',
        'signature_data',
    ];

    protected $casts = [
        'app_date' => 'date',
        'signed' => 'boolean',
        'signed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::created(function (self $model) {
            if ($model->encrypted_id) {
                return;
            }

            $model->encrypted_id = static::makeEncryptedId((string) $model->getKey());
            $model->saveQuietly();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'encrypted_id';
    }

    public static function makeEncryptedId(string $id): string
    {
        $encrypted = Crypt::encryptString($id);

        return rtrim(strtr($encrypted, '+/', '-_'), '=');
    }

    public function applicant(): HasOne
    {
        return $this->hasOne(ConsentApplicant::class, 'application_id');
    }

    public function contact(): HasOne
    {
        return $this->hasOne(ConsentContact::class, 'application_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(ConsentAddress::class, 'application_id');
    }

    public function incomeDocuments(): HasMany
    {
        return $this->hasMany(ConsentDocumentFile::class, 'application_id');
    }

    public function homeAddress(): HasOne
    {
        return $this->hasOne(ConsentAddress::class, 'application_id')->where('kind', 'home');
    }

    public function workAddress(): HasOne
    {
        return $this->hasOne(ConsentAddress::class, 'application_id')->where('kind', 'work');
    }

    public function referenceAddress(): HasOne
    {
        return $this->hasOne(ConsentAddress::class, 'application_id')->where('kind', 'reference');
    }

    public function documentAddress(): HasOne
    {
        return $this->hasOne(ConsentAddress::class, 'application_id')->where('kind', 'document');
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
