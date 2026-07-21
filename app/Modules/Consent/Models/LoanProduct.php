<?php

namespace App\Modules\Consent\Models;

use Illuminate\Database\Eloquent\Model;

class LoanProduct extends Model
{
    protected $table = 'loan_products';

    protected $fillable = [
        'name_th',
        'name_en',
        'bot_code',
        'interest_rate_cap',
        'fee_rate',
        'max_loan_term',
        'max_loan_amount',
        'income_threshold',
        'multiplier_low_income',
        'multiplier_high_income',
    ];

    protected $casts = [
        'interest_rate_cap' => 'decimal:2',
        'fee_rate' => 'decimal:4',
        'max_loan_term' => 'integer',
        'max_loan_amount' => 'decimal:2',
        'income_threshold' => 'decimal:2',
        'multiplier_low_income' => 'decimal:2',
        'multiplier_high_income' => 'decimal:2',
    ];
}
