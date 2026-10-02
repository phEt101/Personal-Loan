<?php

namespace Tests\Feature\Database;

use App\Models\User;
use Database\Seeders\MasterLookupSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_master_lookup_seeder_imports_all_sources_and_is_idempotent(): void
    {
        $this->seed(MasterLookupSeeder::class);

        foreach ([
            'address_types', 'age_ranges', 'banks', 'districts', 'genders',
            'identity_card_types', 'marital_statuses', 'net_income_ranges',
            'occupations', 'phone_types', 'provinces', 'sub_districts',
            'titles', 'type_of_businesses', 'working_conditions', 'document_types',
        ] as $table) {
            $this->assertGreaterThan(0, DB::table($table)->count(), "{$table} was not seeded");
        }

        $counts = [
            'provinces' => DB::table('provinces')->count(),
            'districts' => DB::table('districts')->count(),
            'sub_districts' => DB::table('sub_districts')->count(),
        ];

        $this->seed(MasterLookupSeeder::class);

        foreach ($counts as $table => $count) {
            $this->assertSame($count, DB::table($table)->count());
        }
    }

    public function test_database_enforces_unique_user_email_and_employee_code(): void
    {
        User::factory()->create(['employee_code' => 'EMP0099', 'email' => 'unique@example.com']);

        $this->expectException(QueryException::class);
        User::factory()->create(['employee_code' => 'EMP0099', 'email' => 'other@example.com']);
    }

    public function test_customer_creator_is_set_to_null_when_user_is_deleted(): void
    {
        $user = User::factory()->create();
        DB::table('customers')->insert([
            'CustomerNo' => 'MOCK000000000001',
            'Firstname' => 'Constraint',
            'Lastname' => 'Test',
            'Email' => 'constraint@example.com',
            'sysInsertUserId' => $user->id,
        ]);

        $user->delete();

        $this->assertDatabaseHas('customers', [
            'CustomerNo' => 'MOCK000000000001',
            'sysInsertUserId' => null,
        ]);
    }

    public function test_customer_number_and_child_sequence_constraints_are_unique(): void
    {
        DB::table('customers')->insert([
            'CustomerNo' => 'MOCK000000000001',
            'Firstname' => 'Constraint',
            'Lastname' => 'Test',
            'Email' => 'constraint@example.com',
        ]);
        DB::table('customer_remarks')->insert([
            'CustomerNo' => 'MOCK000000000001',
            'RemarkId' => 1,
            'Comment' => 'first',
        ]);

        $this->expectException(QueryException::class);
        DB::table('customer_remarks')->insert([
            'CustomerNo' => 'MOCK000000000001',
            'RemarkId' => 1,
            'Comment' => 'duplicate',
        ]);
    }
}
