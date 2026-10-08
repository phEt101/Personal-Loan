<?php

namespace App\Modules\Settings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Lang;

class Role extends Model
{
    public const ADMIN_SLUG = 'admin';

    public const USER_SLUG = 'user';

    public const MANAGER_SLUG = 'manager';

    public const SYSTEM_SLUGS = [
        self::ADMIN_SLUG,
        self::MANAGER_SLUG,
        self::USER_SLUG,
    ];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function getDisplayNameAttribute(): string
    {
        $translationKey = 'settings::messages.role_'.$this->slug;

        return Lang::has($translationKey) ? __($translationKey) : $this->name;
    }
}
