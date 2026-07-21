<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Consent\Models\OfficerGroup;

class OfficerGroupSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            [
                'name_th' => 'กลุ่ม 1 (บริษัทเฉพาะ)',
                'name_en' => 'Group 1 (Specific Company)',
                'institution_code' => '01',
                'loan_type_code' => '101',
                'p_loan_regulated_code' => '1',
            ],
            [
                'name_th' => 'กลุ่ม 2 (บริษัททั่วไป)',
                'name_en' => 'Group 2 (General Company)',
                'institution_code' => '02',
                'loan_type_code' => '101',
                'p_loan_regulated_code' => '1',
            ],
        ];

        foreach ($groups as $group) {
            OfficerGroup::create($group);
        }
    }
}
