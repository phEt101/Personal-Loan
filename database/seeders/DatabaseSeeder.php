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

        User::query()->firstOrCreate(
            ['email' => 'user@bigmoneyplus.co.th'],
            [
                'name' => 'User1',
                'password' => Hash::make('P@ssw0rd'),
            ]
        );

        User::query()->firstOrCreate(
            ['email' => 'user1@bigmoneyplus.co.th'],
            [
                'name' => 'User2',
                'password' => Hash::make('P@ssw0rd'),
            ]
        );

    }

}


