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
        'birthdate',
        'id_card',
        'passport',
        'nationality',
        'marital_status',
        'education',
        'occupation',
        'government_level',
        'occupation_other',
        'career_field',
        'career_field_other',
        'income',
        'extra_income',
        'extra_income_source',
        'income_country',
        'has_other_debts',
        'other_debt_installment',
        'has_existing_loan',
        'existing_loan_institution_count',
        'existing_loan_total_amount',
    ];

    protected $casts = [
        'birthdate' => 'date',
        'income' => 'decimal:2',
        'extra_income' => 'decimal:2',
        'other_debt_installment' => 'decimal:2',
        'has_other_debts' => 'boolean',
        'has_existing_loan' => 'boolean',
        'existing_loan_institution_count' => 'integer',
        'existing_loan_total_amount' => 'decimal:2',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(ConsentApplication::class, 'application_id');
    }
}
