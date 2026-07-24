<?php

namespace App\Modules\Consent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsentAddress extends Model
{
    protected $table = 'consent_addresses';

    protected $fillable = [
        'applicant_id',
        'kind',
        'residence_status',
        'address_text',
        'address_room',
        'address_no',
        'address_floor',
        'address_village',
        'address_building',
        'address_soi',
        'address_road',
        'address_subdistrict',
        'address_district',
        'address_province',
        'address_postal',
        'birth_place_address',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(ConsentApplicant::class, 'applicant_id');
    }
}
