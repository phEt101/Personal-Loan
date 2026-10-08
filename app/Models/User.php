<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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

    public function delegatedWork(): HasMany
    {
        return $this->hasMany(WorkDelegation::class, 'delegate_user_id');
    }

    public function workDelegations(): HasMany
    {
        return $this->hasMany(WorkDelegation::class, 'delegator_user_id');
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
