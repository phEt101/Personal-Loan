<?php

namespace App\Modules\Consent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $table = 'customers';

    protected $fillable = [
        'customer_code',
        'title',
        'name',
        'name_en',
        'birthdate',
        'id_card',
        'passport',
        'nationality',
        'phone_number',
        'email',
        'address',
        'occupation',
    ];

    protected $casts = [
        'birthdate' => 'date',
    ];

    public function applicants(): HasMany
    {
        return $this->hasMany(ConsentApplicant::class, 'customer_id');
    }
}
