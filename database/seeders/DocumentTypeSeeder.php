<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $documentTypes = [
            ['DocumentCode' => 'national_id', 'DocumentTypeNameTh' => 'สำเนาบัตรประชาชน', 'DocumentTypeNameEn' => 'National ID copy', 'RequirementType' => 'required'],
            ['DocumentCode' => 'house_registration', 'DocumentTypeNameTh' => 'สำเนาทะเบียนบ้าน', 'DocumentTypeNameEn' => 'House registration copy', 'RequirementType' => 'required'],
            ['DocumentCode' => 'statement', 'DocumentTypeNameTh' => 'Statement', 'DocumentTypeNameEn' => 'Statement', 'RequirementType' => 'required'],
            ['DocumentCode' => 'salary_slip', 'DocumentTypeNameTh' => 'สลิปเงินเดือน', 'DocumentTypeNameEn' => 'Salary slip', 'RequirementType' => 'income_primary'],
            ['DocumentCode' => 'name_change', 'DocumentTypeNameTh' => 'ใบเปลี่ยนชื่อ', 'DocumentTypeNameEn' => 'Name change certificate', 'RequirementType' => 'if_available'],
            ['DocumentCode' => 'tax_50_bis', 'DocumentTypeNameTh' => '50 ทวิ', 'DocumentTypeNameEn' => '50 Tawi', 'RequirementType' => 'income_primary'],
            ['DocumentCode' => 'pnd_90_91', 'DocumentTypeNameTh' => 'ภ.งด. 90/91', 'DocumentTypeNameEn' => 'P.N.D. 90/91', 'RequirementType' => 'income_supporting'],
            ['DocumentCode' => 'income_certificate', 'DocumentTypeNameTh' => 'หนังสือรับรองรายได้', 'DocumentTypeNameEn' => 'Income certificate', 'RequirementType' => 'income_primary'],
            ['DocumentCode' => 'other', 'DocumentTypeNameTh' => 'อื่นๆ', 'DocumentTypeNameEn' => 'Other', 'RequirementType' => 'if_available'],
        ];

        foreach ($documentTypes as $index => $documentType) {
            DB::table('document_types')->updateOrInsert(
                ['DocumentTypeNameEn' => $documentType['DocumentTypeNameEn']],
                [...$documentType, 'SortOrder' => $index + 1, 'Active' => true, 'updated_at' => $now, 'created_at' => $now]
            );
        }
    }
}
