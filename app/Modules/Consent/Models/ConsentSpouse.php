<?php

namespace App\Modules\Consent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsentSpouse extends Model
{
    protected $table = 'consent_spouses';

    protected $fillable = [
        'application_id',
        'spouse_title',
        'spouse_name',
        'spouse_phone',
        'spouse_mobile',
        'spouse_education',
        'spouse_occupation',
        'spouse_company',
        'spouse_income',
    ];

    protected $casts = [
        'spouse_income' => 'decimal:2',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(ConsentApplication::class, 'application_id');
    }
}

