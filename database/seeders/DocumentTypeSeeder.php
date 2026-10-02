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
