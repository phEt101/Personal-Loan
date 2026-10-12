<?php

namespace App\Modules\Settings\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property int $id
 * @property string $employee_code
 * @property string $user_type
 * @property int $role_id
 * @property int|null $responsibility_group_id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string|null $note
 * @property bool $is_active
 * @property-read string $full_name
 * @property-read Role|null $role
 * @property-read ResponsibilityGroup|null $responsibilityGroup
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'employee_code',
        'user_type',
        'role_id',
        'responsibility_group_id',
        'first_name',
        'last_name',
        'email',
        'password',
        'note',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function responsibilityGroup(): BelongsTo
    {
        return $this->belongsTo(ResponsibilityGroup::class);
    }

    public function isAdmin(): bool
    {
        return $this->role?->slug === Role::ADMIN_SLUG;
    }

    public function isManager(): bool
    {
        return $this->role?->slug === Role::MANAGER_SLUG;
    }
}
