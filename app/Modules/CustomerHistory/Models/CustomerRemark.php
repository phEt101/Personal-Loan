<?php

namespace App\Modules\CustomerHistory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerRemark extends Model
{
    protected $fillable = [
        'CustomerNo',
        'RemarkId',
        'Comment',
        'InsertDateTime',
        'InsertUserId',
        'UpdateUserId',
        'UpdateDateTime',
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'InsertDateTime' => 'datetime',
            'UpdateDateTime' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'CustomerNo', 'CustomerNo');
    }
}
