<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HMeterMasterSeeder extends Seeder
{
    private const SOURCES = [
        '[dbo].[AddressType].csv' => 'address_types',
        '[dbo].[AgeRange].csv' => 'age_ranges',
        '[dbo].[Bank].csv' => 'banks',
        '[dbo].[District].csv' => 'districts',
        '[dbo].[Gender].csv' => 'genders',
        '[dbo].[IdentityCardType].csv' => 'identity_card_types',
        '[dbo].[MaritalStatus].csv' => 'marital_statuses',
        '[dbo].[NetIncomeRange].csv' => 'net_income_ranges',
        '[dbo].[Occupation].csv' => 'occupations',
        '[dbo].[PhoneType].csv' => 'phone_types',
        '[dbo].[Province].csv' => 'provinces',
        '[dbo].[SubDistrict].csv' => 'sub_districts',
        '[dbo].[Title].csv' => 'titles',
        '[dbo].[TypeOfBusiness].csv' => 'type_of_businesses',
        '[dbo].[WorkingCondition].csv' => 'working_conditions',
    ];

    public function run(): void
    {
        foreach (self::SOURCES as $fileName => $table) {
            $this->seedTable($fileName, $table);
        }
    }

    private function seedTable(string $fileName, string $table): void
    {
        $path = database_path("seeders/data/hmeter/{$fileName}");
        $handle = fopen($path, 'r');
        $headers = fgetcsv($handle);
        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);

        DB::table($table)->truncate();
        $records = [];

        while (($row = fgetcsv($handle)) !== false) {
            $values = array_map(
                static fn ($value) => $value === '' || $value === 'NULL' ? null : $value,
                array_pad($row, count($headers), null)
            );
            $records[] = array_combine($headers, $values);

            if (count($records) === 500) {
                DB::table($table)->insert($records);
                $records = [];
            }
        }

        if ($records !== []) {
            DB::table($table)->insert($records);
        }

        fclose($handle);
    }
}