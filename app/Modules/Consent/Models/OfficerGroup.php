<?php

namespace App\Modules\Consent\Models;

use Illuminate\Database\Eloquent\Model;

class OfficerGroup extends Model
{
    protected $table = 'officer_groups';

    protected $fillable = [
        'name_th',
        'name_en',
        'institution_code',
        'loan_type_code',
        'p_loan_regulated_code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
