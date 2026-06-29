<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostCode extends Model
{
    protected $fillable = [
        'post_code',
        'district',
        'city',
        'province',
        'country_code',
    ];
}

