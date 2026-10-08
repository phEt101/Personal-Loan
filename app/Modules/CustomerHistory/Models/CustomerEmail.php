<?php

namespace App\Modules\CustomerHistory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerEmail extends Model
{
    protected $fillable = [
        'CustomerNo',
        'EmailId',
        'Email',
        'CreateDateTime',
        'CreateUserId',
        'UpdateUserId',
        'UpdateDateTime',
        'Status',
        'Remark',
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'Status' => 'boolean',
            'CreateDateTime' => 'datetime',
            'UpdateDateTime' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'CustomerNo', 'CustomerNo');
    }
}
