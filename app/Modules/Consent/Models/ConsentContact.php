<?php

namespace App\Modules\Consent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsentContact extends Model
{
    protected $table = 'consent_contacts';

    protected $fillable = [
        'application_id',
        'phone_home',
        'phone_mobile',
        'email',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(ConsentApplication::class, 'application_id');
    }
}

