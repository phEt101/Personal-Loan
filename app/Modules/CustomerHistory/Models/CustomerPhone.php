<?php

namespace App\Modules\CustomerHistory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerPhone extends Model
{
    protected $fillable = [
        'CustomerNo',
        'PhoneId',
        'Remark',
        'Phone',
        'PhoneType',
        'PhoneSequense',
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
