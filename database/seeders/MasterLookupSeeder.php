<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterLookupSeeder extends Seeder
{
    private const SOURCES = [
        '[dbo].[AddressType].csv' => 'address_types',
        '[dbo].[AgeRange].csv' => 'age_ranges',
        '[dbo].[Bank].csv' => 'banks',
        '[dbo].[Gender].csv' => 'genders',
        '[dbo].[IdentityCardType].csv' => 'identity_card_types',
        '[dbo].[MaritalStatus].csv' => 'marital_statuses',
        '[dbo].[NetIncomeRange].csv' => 'net_income_ranges',
        '[dbo].[PhoneType].csv' => 'phone_types',
        '[dbo].[Province].csv' => 'provinces',
        '[dbo].[District].csv' => 'districts',
        '[dbo].[SubDistrict].csv' => 'sub_districts',
        '[dbo].[TypeOfBusiness].csv' => 'type_of_businesses',
        '[dbo].[Occupation].csv' => 'occupations',
        '[dbo].[Title].csv' => 'titles',
        '[dbo].[WorkingCondition].csv' => 'working_conditions',
    ];

    private const UNIQUE_KEYS = [
        'address_types' => ['AddressTypeCode'],
        'age_ranges' => ['AgeRangeId'],
        'banks' => ['BankCode'],
        'districts' => ['ProvinceCode', 'DistrictCode'],
        'genders' => ['GenderId'],
        'identity_card_types' => ['IdentityCardTypeCode'],
        'marital_statuses' => ['MaritalStatusCode'],
        'net_income_ranges' => ['NetIncomeRangeId'],
        'occupations' => ['OccupationCode'],
        'phone_types' => ['PhoneTypeCode'],
        'provinces' => ['ProvinceCode'],
        'sub_districts' => ['ProvinceCode', 'DistrictCode', 'SubDistrictCode'],
        'titles' => ['TitleCode'],
        'type_of_businesses' => ['TypeOfBusinessId'],
        'working_conditions' => ['WorkingConditionId'],
    ];

    public function run(): void
    {
        foreach (self::SOURCES as $fileName => $table) {
            $this->seedTable($fileName, $table);
        }
    }

    private function seedTable(string $fileName, string $table): void
    {
        $path = database_path("seeders/data/master/{$fileName}");
        $handle = fopen($path, 'r');
        $headers = fgetcsv($handle);
        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);

        $records = [];

        while (($row = fgetcsv($handle)) !== false) {
            $values = array_map(
                static fn ($value) => $value === '' || $value === 'NULL' ? null : $value,
                array_pad($row, count($headers), null)
            );
            $records[] = array_combine($headers, $values);

            if (count($records) === 500) {
                $this->upsertRecords($table, $records);
                $records = [];
            }
        }

        if ($records !== []) {
            $this->upsertRecords($table, $records);
        }

        fclose($handle);
    }

    private function upsertRecords(string $table, array $records): void
    {
        $columns = array_keys($records[0]);
        $updateColumns = array_values(array_diff($columns, self::UNIQUE_KEYS[$table]));

        DB::table($table)->upsert($records, self::UNIQUE_KEYS[$table], $updateColumns);
    }
}
