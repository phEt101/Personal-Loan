<?php

namespace Database\Seeders;

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
        $this->call(HMeterMasterSeeder::class);

        $internalEmployeeCodes = collect(range(1, 2))
            ->map(fn (int $number) => 'EMP'.str_pad((string) $number, 4, '0', STR_PAD_LEFT));

        User::query()
            ->whereIn('employee_code', $internalEmployeeCodes)
            ->get()
            ->each(fn (User $user) => $user->update([
                'email' => strtolower($user->employee_code).'@seed-temp.invalid',
            ]));

        for ($number = 1; $number <= 2; $number++) {
            User::query()->updateOrCreate(
                ['employee_code' => 'EMP'.str_pad((string) $number, 4, '0', STR_PAD_LEFT)],
                [
                    'user_type' => 'internal',
                    'first_name' => 'User',
                    'last_name' => $number === 1 ? 'One' : 'Two',
                    'email' => 'user'.$number.'@bigmoneyplus.co.th',
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
