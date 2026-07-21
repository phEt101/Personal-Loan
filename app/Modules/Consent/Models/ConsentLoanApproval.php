<?php

namespace App\Modules\Consent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsentLoanApproval extends Model
{
    protected $table = 'consent_loan_approvals';

    protected $fillable = [
        'application_id',
        'interest_rate',
        'fee_rate',
        'loan_amount',
        'late_penalty_rate',
        'installments',
        'monthly_payment',
        'monthly_payment_raw',
        'total_interest',
        'total_contract_amount',
    ];

    protected $casts = [
        'interest_rate' => 'decimal:2',
        'fee_rate' => 'decimal:2',
        'loan_amount' => 'decimal:2',
        'late_penalty_rate' => 'decimal:2',
        'monthly_payment' => 'decimal:2',
        'monthly_payment_raw' => 'decimal:2',
        'total_interest' => 'decimal:2',
        'total_contract_amount' => 'decimal:2',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(ConsentApplication::class, 'application_id');
    }
}
