<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(MasterLookupSeeder::class);

        $adminRoleId = Role::query()->where('slug', Role::ADMIN_SLUG)->valueOrFail('id');
        $userRoleId = Role::query()->where('slug', Role::USER_SLUG)->valueOrFail('id');

        $internalUsers = [
            [
                'employee_code' => 'EMP0001',
                'role_id' => $adminRoleId,
                'first_name' => 'ธิดารัตน์',
                'last_name' => 'มูลเทพ',
                'email' => 'Tidarat@bigmoneyplus.co.th',
            ],
            [
                'employee_code' => 'EMP0002',
                'role_id' => $userRoleId,
                'first_name' => 'ทัศนีย์',
                'last_name' => 'จุฑารัตน์จรัส',
                'email' => 'Thatsanee@bigmoneyplus.co.th',
            ],
            [
                'employee_code' => 'EMP0003',
                'role_id' => $userRoleId,
                'first_name' => 'ณัชชา',
                'last_name' => 'สมบุญ',
                'email' => 'Natcha@bigmoneyplus.co.th',
            ],
        ];

        $internalEmployeeCodes = collect($internalUsers)->pluck('employee_code');

        User::query()
            ->whereIn('employee_code', $internalEmployeeCodes)
            ->get()
            ->each(fn (User $user) => $user->update([
                'email' => strtolower($user->employee_code).'@seed-temp.invalid',
            ]));

        foreach ($internalUsers as $internalUser) {
            User::query()->updateOrCreate(
                ['employee_code' => $internalUser['employee_code']],
                $internalUser + [
                    'user_type' => 'internal',
                    'password' => Hash::make('P@ssw0rd'),
                    'note' => 'ผู้ใช้งานภายในสำหรับทดสอบระบบ',
                ]
            );
        }

        $externalEmployeeCodes = collect(range(1, 8))
            ->map(fn (int $number) => 'EXT'.str_pad((string) $number, 4, '0', STR_PAD_LEFT));

        User::query()
            ->whereIn('employee_code', $externalEmployeeCodes)
            ->get()
            ->each(fn (User $user) => $user->update([
                'email' => strtolower($user->employee_code).'@seed-temp.invalid',
            ]));

        for ($number = 1; $number <= 8; $number++) {
            User::query()->updateOrCreate(
                ['employee_code' => 'EXT'.str_pad((string) $number, 4, '0', STR_PAD_LEFT)],
                [
                    'user_type' => 'external',
                    'role_id' => $userRoleId,
                    'first_name' => 'Wipay',
                    'last_name' => 'User '.$number,
                    'email' => 'user'.$number.'@wipay.co.th',
                    'password' => Hash::make('wipay'.str_pad((string) $number, 2, '0', STR_PAD_LEFT)),
                    'note' => 'ผู้ใช้งานภายนอก Wipay',
                ]
            );
        }
    }
}
