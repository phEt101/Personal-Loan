<?php

namespace App\Modules\Consent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsentDisbursementAccount extends Model
{
    protected $table = 'consent_disbursement_accounts';

    protected $fillable = [
        'application_id',
        'bank_name',
        'bank_branch',
        'account_name',
        'account_type',
        'account_number',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(ConsentApplication::class, 'application_id');
    }
}

