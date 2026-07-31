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
        // Produce fixed-length 50-character token:
        // - first 18 chars: base62-encoded (left-zero-padded) XORed id
        // - last 32 chars: hex HMAC-SHA256 of the 18-char payload using APP_KEY
        $key = config('app.key') ?: env('APP_KEY', '');
        $mask = hexdec(substr(hash('sha256', $key), 0, 12));
        $val = ((int) $id) ^ $mask;

        $payload = static::base62Encode($val);
        if (strlen($payload) > 18) {
            // Should not happen for typical id ranges; fallback to hash-based token
            $full = hash('sha256', $key . '|' . $id);
            return substr($full, 0, 50);
        }

        $payload = str_pad($payload, 18, '0', STR_PAD_LEFT);
        $hmac = hash_hmac('sha256', $payload, $key);
        $token = $payload . substr($hmac, 0, 32);

        return $token;
    }

    /**
     * Decode an encrypted_id (hash) back to numeric id, or null if invalid.
     */
    public static function decodeEncryptedId(string $hash): ?int {
        $key = config('app.key') ?: env('APP_KEY', '');

        if (strlen($hash) !== 50) {
            return null;
        }

        $payload = substr($hash, 0, 18);
        $suffix = substr($hash, 18, 32);

        $expected = substr(hash_hmac('sha256', $payload, $key), 0, 32);
        if (!hash_equals($expected, $suffix)) {
            return null;
        }

        // remove left padding zeros
        $unpad = ltrim($payload, '0');
        if ($unpad === '') $unpad = '0';

        $val = static::base62Decode($unpad);
        if ($val === null) return null;

        $mask = hexdec(substr(hash('sha256', $key), 0, 12));
        $id = ((int) $val) ^ $mask;

        return $id >= 0 ? $id : null;
    }

    private static function base62Encode(int $num): string {
        if ($num === 0) return '0';
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $base = 62;
        $out = '';
        $n = $num;
        while ($n > 0) {
            $rem = $n % $base;
            $out = $chars[$rem] . $out;
            $n = intdiv($n, $base);
        }
        return $out;
    }

    private static function base62Decode(string $str): ?int {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $base = 62;
        $len = strlen($str);
        if ($len === 0) return null;
        $n = 0;
        for ($i = 0; $i < $len; $i++) {
            $pos = strpos($chars, $str[$i]);
            if ($pos === false) return null;
            $n = $n * $base + $pos;
        }
        return $n;
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

    public function incomeDocuments(): HasManyThrough
    {
        return $this->hasManyThrough(
            ConsentDocumentFile::class,
            ConsentApplicant::class,
            'application_id', // foreign on applicants
            'applicant_id',    // foreign on documents
            'id',
            'id'
        );
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
        )->where('consent_addresses.kind', 'reference/guarantor');
    }

    public function referenceWorkAddress(): HasOneThrough
    {
        return $this->hasOneThrough(
            ConsentAddress::class,
            ConsentApplicant::class,
            'application_id',
            'applicant_id',
            'id',
            'id'
        )->where('consent_addresses.kind', 'reference/guarantor_work');
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

    public function employment(): HasOneThrough
    {
        return $this->hasOneThrough(
            ConsentEmployment::class,
            ConsentApplicant::class,
            'application_id', // foreign key on applicants
            'applicant_id',    // foreign key on employments
            'id',
            'id'
        );
    }

    public function previousEmployment(): HasOneThrough
    {
        return $this->hasOneThrough(
            ConsentPreviousEmployment::class,
            ConsentApplicant::class,
            'application_id', // foreign key on applicants
            'applicant_id',    // foreign key on previous_employments
            'id',
            'id'
        );
    }

    public function reference(): HasOneThrough
    {
        return $this->hasOneThrough(
            ConsentReference::class,
            ConsentApplicant::class,
            'application_id', // foreign key on applicants
            'applicant_id',    // foreign key on references
            'id',
            'id'
        );
    }

    public function loanRequest(): HasOne
    {
        return $this->hasOne(ConsentLoanRequest::class, 'application_id');
    }

    public function disbursementAccount(): HasOneThrough
    {
        return $this->hasOneThrough(
            ConsentDisbursementAccount::class,
            ConsentApplicant::class,
            'application_id',
            'applicant_id',
            'id',
            'id'
        );
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
