<?php

namespace App\Modules\Consent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsentReference extends Model
{
    protected $table = 'consent_references';

    protected $fillable = [
        'application_id',
        'ref_name',
        'ref_relation',
        'ref_phone_home',
        'ref_phone_mobile',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(ConsentApplication::class, 'application_id');
    }
}

