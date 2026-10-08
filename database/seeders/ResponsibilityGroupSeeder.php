<?php

namespace Database\Seeders;

use App\Modules\Settings\Models\ResponsibilityGroup;
use App\Modules\Settings\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ResponsibilityGroupSeeder extends Seeder
{
    private const GROUPS = [
        [
            'name' => 'A',
            'internal_users' => ['EMP0002'],
            'external_users' => ['EXT0004', 'EXT0005'],
        ],
        [
            'name' => 'B',
            'internal_users' => ['EMP0003'],
            'external_users' => ['EXT0006', 'EXT0007'],
        ],
    ];

    public function run(): void
    {
        DB::transaction(function (): void {
            foreach (self::GROUPS as $groupData) {
                $group = ResponsibilityGroup::query()->updateOrCreate(
                    ['name' => $groupData['name']],
                    ['is_active' => true]
                );

                User::query()
                    ->where('user_type', 'internal')
                    ->whereIn('employee_code', $groupData['internal_users'])
                    ->update(['responsibility_group_id' => $group->getKey()]);
                User::query()
                    ->where('user_type', 'external')
                    ->whereIn('employee_code', $groupData['external_users'])
                    ->update(['responsibility_group_id' => $group->getKey()]);
            }
        });
    }
}
