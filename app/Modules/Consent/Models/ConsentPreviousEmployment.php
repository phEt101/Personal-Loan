<?php

namespace App\Modules\Consent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsentPreviousEmployment extends Model
{
    protected $table = 'consent_previous_employments';

    protected $fillable = [
        'applicant_id',
        'previous_company_name',
        'previous_position',
        'previous_income',
        'previous_address',
        'previous_phone',
    ];

    protected $casts = [
        'previous_income' => 'decimal:2',
    ];

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(ConsentApplicant::class, 'applicant_id');
    }
}
