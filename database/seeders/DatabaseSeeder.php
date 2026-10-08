<?php

namespace Database\Seeders;

use App\Modules\Settings\Models\Role;
use App\Modules\Settings\Models\User;
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

        Role::query()->updateOrCreate(
            ['slug' => Role::MANAGER_SLUG],
            [
                'name' => 'Manager',
                'description' => 'Can view customer records created by external users.',
                'is_active' => true,
            ]
        );

        $adminRoleId = Role::query()->where('slug', Role::ADMIN_SLUG)->valueOrFail('id');
        $managerRoleId = Role::query()->where('slug', Role::MANAGER_SLUG)->valueOrFail('id');
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

        $externalManagers = [
            [
                'employee_code' => 'EXT0001',
                'first_name' => 'ภูเบศ',
                'last_name' => 'จุลบล',
                'email' => 'phubeth.jul@wipay.co.th',
            ],
            [
                'employee_code' => 'EXT0002',
                'first_name' => 'วันวิสาข์',
                'last_name' => 'เรืองฉิม',
                'email' => 'wanvisa.rue@wipay.co.th',
            ],
            [
                'employee_code' => 'EXT0003',
                'first_name' => 'ญาโณทัย',
                'last_name' => 'เหมวัฒน์',
                'email' => 'Yanothai.hem@wipay.co.th',
            ],
        ];

        User::query()
            ->whereIn('employee_code', collect($externalManagers)->pluck('employee_code'))
            ->get()
            ->each(fn (User $user) => $user->update([
                'email' => strtolower($user->employee_code).'@seed-temp.invalid',
            ]));

        foreach ($externalManagers as $externalManager) {
            User::query()->updateOrCreate(
                ['employee_code' => $externalManager['employee_code']],
                $externalManager + [
                    'user_type' => 'external',
                    'role_id' => $managerRoleId,
                    'password' => Hash::make('P@ssw0rd'),
                    'note' => 'ผู้จัดการภายนอก Wipay',
                ]
            );
        }

        $externalUsers = [
            [
                'employee_code' => 'EXT0004',
                'first_name' => 'ธีรศาสนติ์',
                'last_name' => 'เมธาปัฐวีร์',
                'email' => 'Theerasarn@wipay.co.th',
            ],
            [
                'employee_code' => 'EXT0005',
                'first_name' => 'ฐิติมา',
                'last_name' => 'แซ่ลิ้ม',
                'email' => 'Thitima@wipay.co.th',
            ],
            [
                'employee_code' => 'EXT0006',
                'first_name' => 'นาซือเร๊าะ',
                'last_name' => 'สาและ',
                'email' => 'Naserah@wipay.co.th',
            ],
            [
                'employee_code' => 'EXT0007',
                'first_name' => 'วทัญญู',
                'last_name' => 'กลับสังข์',
                'email' => 'Wathanyu@wipay.co.th',
            ],
        ];

        foreach (range(8, 11) as $number) {
            $externalUsers[] = [
                'employee_code' => 'EXT'.str_pad((string) $number, 4, '0', STR_PAD_LEFT),
                'first_name' => 'Wipay',
                'last_name' => 'User '.$number,
                'email' => 'user'.$number.'@wipay.co.th',
            ];
        }

        $externalEmployeeCodes = collect($externalUsers)->pluck('employee_code');

        User::query()
            ->whereIn('employee_code', $externalEmployeeCodes)
            ->get()
            ->each(fn (User $user) => $user->update([
                'email' => strtolower($user->employee_code).'@seed-temp.invalid',
            ]));

        foreach ($externalUsers as $externalUser) {
            User::query()->updateOrCreate(
                ['employee_code' => $externalUser['employee_code']],
                $externalUser + [
                    'user_type' => 'external',
                    'role_id' => $userRoleId,
                    'password' => Hash::make('P@ssw0rd'),
                    'note' => 'ผู้ใช้งานภายนอก Wipay',
                ]
            );
        }

        $this->call(ResponsibilityGroupSeeder::class);
    }
}
