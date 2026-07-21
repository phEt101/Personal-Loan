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
        Schema::disableForeignKeyConstraints();
        DB::table('users')->truncate();
        DB::table('post_codes')->truncate();
        DB::table('loan_products')->truncate();
        DB::table('officer_groups')->truncate();
        Schema::enableForeignKeyConstraints();

        $this->call([
            PostCodeSeeder::class,
            LoanProductSeeder::class,
            OfficerGroupSeeder::class,
        ]);
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


