<?php

namespace App\Modules\Consent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsentContact extends Model
{
    protected $table = 'consent_contacts';

    protected $fillable = [
        'applicant_id',
        'phone_home',
        'phone_mobile',
        'email',
    ];

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(ConsentApplicant::class, 'applicant_id');
    }
}

