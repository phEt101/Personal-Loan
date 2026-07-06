<?php

namespace App\Modules\Consent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsentDocumentDelivery extends Model
{
    protected $table = 'consent_document_deliveries';

    protected $fillable = [
        'application_id',
        'document_delivery',
        'document_email',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(ConsentApplication::class, 'application_id');
    }
}

