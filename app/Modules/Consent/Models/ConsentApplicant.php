<?php

namespace App\Modules\Consent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsentApplicant extends Model
{
    protected $table = 'consent_request_applicants';

    protected $fillable = [
        'application_id',
        'title',
        'name',
        'name_en',
        'dob',
        'id_card',
        'gender',
        'age',
        'nationality',
        'marital_status',
        'education',
        'occupation',
        'income',
        'extra_income',
        'extra_income_source',
        'business_income',
        'average_monthly_income',
        'has_other_debts',
        'other_debt_installment',
        'has_existing_loan',
    ];

    protected $casts = [
        'dob' => 'date',
        'income' => 'decimal:2',
        'extra_income' => 'decimal:2',
        'average_monthly_income' => 'decimal:2',
        'other_debt_installment' => 'decimal:2',
        'has_other_debts' => 'boolean',
        'has_existing_loan' => 'boolean',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(ConsentApplication::class, 'application_id');
    }
}
