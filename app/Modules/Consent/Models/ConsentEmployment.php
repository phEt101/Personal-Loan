<?php

namespace App\Modules\Consent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsentEmployment extends Model
{
    protected $table = 'consent_employments';

    protected $fillable = [
        'applicant_id',
        'use_home_address',
        'company_name',
        'business_type',
        'work_department',
        'work_years',
        'work_months',
        'work_phone',
    ];

    protected $casts = [
        'use_home_address' => 'boolean',
    ];

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(ConsentApplicant::class, 'applicant_id');
    }
}
