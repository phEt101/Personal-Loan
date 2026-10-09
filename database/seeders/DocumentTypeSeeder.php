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
            ['DocumentTypeNameTh' => 'สำเนาบัตรประชาชน', 'DocumentTypeNameEn' => 'National ID copy'],
            ['DocumentTypeNameTh' => 'สำเนาทะเบียนบ้าน', 'DocumentTypeNameEn' => 'House registration copy'],
            ['DocumentTypeNameTh' => 'Statement', 'DocumentTypeNameEn' => 'Statement'],
            ['DocumentTypeNameTh' => 'สลิปเงินเดือน', 'DocumentTypeNameEn' => 'Salary slip'],
            ['DocumentTypeNameTh' => 'ใบเปลี่ยนชื่อ', 'DocumentTypeNameEn' => 'Name change certificate'],
            ['DocumentTypeNameTh' => '50 ทวิ/ ภ.งด. 90/91', 'DocumentTypeNameEn' => '50 Tawi / P.N.D. 90/91'],
            ['DocumentTypeNameTh' => 'หนังสือรับรองรายได้', 'DocumentTypeNameEn' => 'Income certificate'],
            ['DocumentTypeNameTh' => 'อื่นๆ', 'DocumentTypeNameEn' => 'Other'],
        ];

        foreach ($documentTypes as $index => $documentType) {
            DB::table('document_types')->updateOrInsert(
                ['DocumentTypeNameEn' => $documentType['DocumentTypeNameEn']],
                [...$documentType, 'SortOrder' => $index + 1, 'Active' => true, 'updated_at' => $now, 'created_at' => $now]
            );
        }
    }
}
