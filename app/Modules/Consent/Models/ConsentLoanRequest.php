<?php

namespace App\Modules\Consent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsentLoanRequest extends Model
{
    protected $table = 'consent_loan_requests';

    protected $fillable = [
        'application_id',
        'loan_term',
        'loan_amount_type',
        'custom_loan_amount',
        'loan_purpose',
    ];

    protected $casts = [
        'custom_loan_amount' => 'decimal:2',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(ConsentApplication::class, 'application_id');
    }
}

