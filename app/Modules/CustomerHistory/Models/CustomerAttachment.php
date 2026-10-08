<?php

namespace App\Modules\CustomerHistory\Models;

use App\Modules\Settings\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerAttachment extends Model
{
    protected $fillable = [
        'CustomerNo',
        'DocumentName',
        'DocumentTypeId',
        'OriginalName',
        'FilePath',
        'MimeType',
        'FileSize',
        'UploadedBy',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'CustomerNo', 'CustomerNo');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'UploadedBy');
    }
}
