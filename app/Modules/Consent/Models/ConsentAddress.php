<?php

namespace App\Modules\Consent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsentAddress extends Model
{
    protected $table = 'consent_addresses';

    protected $fillable = [
        'application_id',
        'kind',
        'dwelling_type',
        'residence_status',
        'residence_rent_amount',
        'residence_years',
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
    ];

    protected $casts = [
        'residence_rent_amount' => 'decimal:2',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(ConsentApplication::class, 'application_id');
    }
}

