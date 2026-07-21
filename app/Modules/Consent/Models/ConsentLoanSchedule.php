<?php

namespace App\Modules\Consent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsentLoanSchedule extends Model
{
    protected $table = 'consent_loan_schedules';

    protected $fillable = [
        'application_id',
        'installment_no',
        'due_date',
        'payment_amount',
        'principal_amount',
        'interest_amount',
        'fee_amount',
        'remaining_principal',
    ];

    protected $casts = [
        'due_date' => 'date',
        'payment_amount' => 'decimal:2',
        'principal_amount' => 'decimal:2',
        'interest_amount' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'remaining_principal' => 'decimal:2',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(ConsentApplication::class, 'application_id');
    }
}
