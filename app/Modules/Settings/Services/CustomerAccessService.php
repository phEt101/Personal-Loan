<?php

namespace App\Modules\Settings\Services;

use App\Modules\Settings\Models\User;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CustomerAccessService
{
    public function accessibleExternalGroupIds(User $user): ?Collection
    {
        if ($user->isAdmin()) {
            return null;
        }

        $groupIds = collect();
        if ($user->responsibility_group_id !== null) {
            $groupIds->push((int) $user->responsibility_group_id);
        }

        return $groupIds
            ->merge(DB::table('work_delegations as delegation')
                ->join('responsibility_groups as delegated_group', 'delegated_group.id', '=', 'delegation.responsibility_group_id')
                ->where('delegation.delegate_user_id', $user->id)
                ->where('delegated_group.is_active', true)
                ->whereNull('delegation.cancelled_at')
                ->where('delegation.starts_at', '<=', now())
                ->where('delegation.ends_at', '>=', now())
                ->pluck('delegation.responsibility_group_id'))
            ->map(fn ($groupId) => (int) $groupId)
            ->unique()
            ->values();
    }

    public function applyReadScope(
        EloquentBuilder|QueryBuilder $query,
        User $user,
        string $customerTable = 'customers'
    ): void
    {
        if ($user->user_type === 'internal') {
            if (! $user->isAdmin()) {
                $this->applyInternalScope($query, $user, $customerTable);
            }

            return;
        }

        if ($user->isManager()) {
            $query->whereIn("{$customerTable}.sysInsertUserId", DB::table('users')
                ->where('user_type', 'external')
                ->select('id'));

            return;
        }

        $query->where("{$customerTable}.sysInsertUserId", $user->getAuthIdentifier());
    }

    private function applyInternalScope(
        EloquentBuilder|QueryBuilder $query,
        User $user,
        string $customerTable
    ): void
    {
        $query->where(function ($query) use ($customerTable, $user) {
            $query->whereIn("{$customerTable}.sysInsertUserId", DB::table('users')
                ->where('user_type', 'internal')
                ->select('id'));

            if ($user->responsibility_group_id !== null) {
                $query->orWhereIn("{$customerTable}.sysInsertUserId", DB::table('users')
                    ->where('user_type', 'external')
                    ->where('responsibility_group_id', $user->responsibility_group_id)
                    ->select('id'));
            }

            $query->orWhereIn("{$customerTable}.sysInsertUserId", DB::table('users as delegated_external')
                ->join('work_delegations as delegation', 'delegation.responsibility_group_id', '=', 'delegated_external.responsibility_group_id')
                ->join('responsibility_groups as delegated_group', 'delegated_group.id', '=', 'delegation.responsibility_group_id')
                ->where('delegated_external.user_type', 'external')
                ->where('delegated_group.is_active', true)
                ->where('delegation.delegate_user_id', $user->id)
                ->whereNull('delegation.cancelled_at')
                ->where('delegation.starts_at', '<=', now())
                ->where('delegation.ends_at', '>=', now())
                ->select('delegated_external.id'));
        });
    }
}
