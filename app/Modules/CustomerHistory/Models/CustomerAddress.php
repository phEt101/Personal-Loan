<?php

namespace App\Modules\CustomerHistory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerAddress extends Model
{
    protected $fillable = [
        'CustomerNo',
        'AddressId',
        'AddressLine1',
        'AddressLine2',
        'ProvinceCode',
        'ProvinceDesc',
        'DistrictCode',
        'DistrictDesc',
        'SubDistrictCode',
        'SubDistrictDesc',
        'ZipCode',
        'AddressTypeCode',
        'Remark',
        'Status',
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return ['Status' => 'boolean'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'CustomerNo', 'CustomerNo');
    }
}
