<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
            ]
        );

        User::query()->firstOrCreate(
            ['email' => 'test1@example.com'],
            [
                'name' => 'Test User1',
                'password' => Hash::make('password'),
            ]
        );

        if (Schema::hasTable('consent_requests')) {
            Schema::disableForeignKeyConstraints();
            foreach ([
                'consent_documents_file',
                'consent_disbursement_accounts',
                'consent_loan_requests',
                'consent_references',
                'consent_previous_employments',
                'consent_employments',
                'consent_addresses',
                'consent_contacts',
                'consent_request_applicants',
                'consent_requests',
            ] as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                }
            }
            Schema::enableForeignKeyConstraints();
        }

        $this->call(PostCodeSeeder::class);
    }

}


