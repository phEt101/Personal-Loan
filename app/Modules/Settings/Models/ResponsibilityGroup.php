<?php

namespace App\Modules\Settings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResponsibilityGroup extends Model
{
    protected $fillable = ['name', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function internalUsers(): HasMany
    {
        return $this->hasMany(User::class)->where('user_type', 'internal');
    }

    public function externalUsers(): HasMany
    {
        return $this->hasMany(User::class)->where('user_type', 'external');
    }

}
