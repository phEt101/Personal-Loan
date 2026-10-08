<?php

namespace App\Modules\CustomerHistory\Services;

use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CustomerAccessService
{
    public function applyReadScope(Builder $query, User $user, string $customerTable = 'customers'): void
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

    private function applyInternalScope(Builder $query, User $user, string $customerTable): void
    {
        $query->where(function (Builder $query) use ($customerTable, $user) {
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
