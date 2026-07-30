<?php

namespace App\Modules\Consent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsentReference extends Model
{
    protected $table = 'consent_references';

    protected $fillable = [
        'applicant_id',
        'ref_name',
        'ref_type',
        'ref_relation',
        'ref_phone_home',
        'ref_phone_mobile',
        'birthdate',
        'nationality',
        'marital_status',
        'education',
        'education_other',
        'occupation',
        'government_level',
        'occupation_other',
        'career_field',
        'career_field_other',
        'extra_income_source',
        'extra_income_source_other',
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
        'existing_loan_total_amount' => 'decimal:2',
        'has_other_debts' => 'boolean',
        'has_existing_loan' => 'boolean',
    ];

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(ConsentApplicant::class, 'applicant_id');
    }
}

