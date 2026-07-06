<?php

namespace App\Modules\Consent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsentPreviousEmployment extends Model
{
    protected $table = 'consent_previous_employments';

    protected $fillable = [
        'application_id',
        'previous_company_name',
        'previous_business_type',
        'previous_position',
        'previous_income',
        'previous_work_years',
        'previous_phone',
    ];

    protected $casts = [
        'previous_income' => 'decimal:2',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(ConsentApplication::class, 'application_id');
    }
}

