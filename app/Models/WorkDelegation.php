<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkDelegation extends Model
{
    protected $fillable = [
        'responsibility_group_id',
        'delegator_user_id',
        'delegate_user_id',
        'starts_at',
        'ends_at',
        'note',
        'created_by',
        'cancelled_at',
        'cancelled_by',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(ResponsibilityGroup::class, 'responsibility_group_id');
    }

    public function delegator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delegator_user_id');
    }

    public function delegate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delegate_user_id');
    }

    public function getStatusAttribute(): string
    {
        if ($this->cancelled_at !== null) {
            return 'cancelled';
        }

        if ($this->starts_at->isFuture()) {
            return 'pending';
        }

        return $this->ends_at->isPast() ? 'ended' : 'active';
    }
}
