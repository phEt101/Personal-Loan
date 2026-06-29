<?php

namespace Database\Seeders;

use App\Modules\Consent\Models\ConsentForm;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (!User::where('email', 'test@example.com')->exists()) {
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }

        if (Schema::hasTable('consent_forms')) {
            Schema::disableForeignKeyConstraints();
            DB::table('consent_forms')->truncate();
            Schema::enableForeignKeyConstraints();
        }

        foreach ($this->buildConsentForms() as $index => $attributes) {
            ConsentForm::create($this->makeConsentFormPayload($attributes, $index + 1));
        }

        $this->call(PostCodeSeeder::class);
    }

    private function buildConsentForms(): array
    {
        return [
            [
                'title' => 'นาย',
                'name' => 'ณัฐพงศ์ วัฒนศิริ',
                'name_en' => 'NATTHAPHONG WATTANASIRI',
                'gender' => 'ชาย',
                'dob' => '1992-11-18',
                'id_card' => '1101701234567',
                'age' => 33,
                'nationality' => 'ไทย',
                'marital_status' => 'โสด',
                'education' => 'ปริญญาตรี',
                'occupation' => 'พนักงานบริษัท',
                'income' => 38000,
                'extra_income' => 5000,
                'extra_income_source' => 'งานฟรีแลนซ์ (กราฟิก)',
                'has_other_debts' => 'มี',
                'other_debt_installment' => 12000,
                'dwelling_type' => 'ทาวน์เฮาส์',
                'residence_status' => 'เช่า/ผ่อนชำระ',
                'residence_rent_amount' => 8500,
                'residence_years' => 3,
                'address_no' => '88/12',
                'address_floor' => '-',
                'address_village' => '3',
                'address_building' => 'หมู่บ้านเดอะกรีนวิลล์',
                'address_soi' => 'สุขุมวิท 103',
                'address_road' => 'สุขุมวิท',
                'address_subdistrict' => 'บางนา',
                'address_district' => 'บางนา',
                'address_province' => 'กรุงเทพมหานคร',
                'address_postal' => '10260',
                'phone_mobile' => '0891234567',
                'email' => 'natthaphong.demo@example.com',
                'line_id' => 'natthaphong_w',
                'use_home_address' => true,
                'company_type' => 'บจก.',
                'company_name' => 'บริษัท โซลูชั่น จำกัด',
                'business_type' => 'เทคโนโลยีสารสนเทศ',
                'work_occupation' => 'พนักงานออฟฟิศ',
                'work_position' => 'เจ้าหน้าที่วิเคราะห์ระบบ',
                'work_years' => 4,
                'work_months' => 2,
                'document_delivery' => 'E-mail',
                'document_email' => 'natthaphong.demo@example.com',
                'loan_term' => 24,
                'loan_amount_type' => 'custom',
                'custom_loan_amount' => 120000,
                'loan_purpose' => 'ปิดหนี้และรวมหนี้',
                'bank_name' => 'ธนาคารกสิกรไทย',
                'bank_branch' => 'บางนา',
                'account_name' => 'ณัฐพงศ์ วัฒนศิริ',
                'account_type' => 'ออมทรัพย์',
                'account_number' => '123456789012',
            ],
            [
                'title' => 'นางสาว',
                'name' => 'พรทิพย์ สดใส',
                'name_en' => 'PORNTHIP SODSAI',
                'gender' => 'หญิง',
                'age' => 19,
                'income' => 21000,
                'has_other_debts' => 'ไม่มี',
                'other_debt_installment' => 0,
            ],
            [
                'title' => 'นาย',
                'name' => 'วิชัย เกษม',
                'name_en' => 'WICHAI KASEM',
                'gender' => 'ชาย',
                'age' => 51,
                'income' => 27000,
                'has_other_debts' => 'มี',
                'other_debt_installment' => 7000,
            ],
            [
                'title' => 'นาง',
                'name' => 'ศิริพร เงินดี',
                'name_en' => 'SIRIPORN NGERNDEE',
                'gender' => 'หญิง',
                'age' => 28,
                'income' => 14999,
                'has_other_debts' => 'ไม่มี',
                'other_debt_installment' => 0,
                'has_existing_loan' => 'ใช่',
                'document_delivery' => 'E-mail',
                'document_email' => 'siriporn.seed@example.com',
            ],
            [
                'title' => 'นาย',
                'name' => 'ประยูร ภูผา',
                'name_en' => 'PRAYOON PUPHA',
                'gender' => 'ชาย',
                'age' => 40,
                'income' => 24000,
                'has_other_debts' => 'มี',
                'other_debt_installment' => 12001,
                'loan_amount_type' => 'custom',
                'custom_loan_amount' => 80000,
            ],
            [
                'title' => 'นาง',
                'name' => 'สุดารัตน์ มั่นคง',
                'name_en' => 'SUDARAT MANKONG',
                'gender' => 'หญิง',
                'age' => 38,
                'marital_status' => 'สมรส',
                'income' => 45000,
                'has_other_debts' => 'มี',
                'other_debt_installment' => 9000,
                'spouse_title' => 'นาย',
                'spouse_name' => 'ชาญชัย มั่นคง',
                'spouse_phone' => '021234501',
                'spouse_mobile' => '0891234501',
                'spouse_education' => 'ปริญญาตรี',
                'spouse_occupation' => 'พนักงานบริษัท',
                'spouse_company' => 'Bright Future Co.,Ltd.',
                'spouse_income' => 28000,
            ],
            [
                'title' => 'นาย',
                'name' => 'ธนา วิริยะ',
                'name_en' => 'THANA WIRIYA',
                'gender' => 'ชาย',
                'age' => 20,
                'income' => 15000,
                'has_other_debts' => 'ไม่มี',
                'other_debt_installment' => 0,
                'use_home_address' => true,
                'work_years' => 0,
                'work_months' => 8,
                'previous_company_name' => 'Old Growth Ltd.',
                'previous_business_type' => 'ค้าปลีก',
                'previous_position' => 'พนักงานขาย',
                'previous_income' => 22000,
                'previous_work_years' => 2,
                'previous_phone' => '026661234',
                'document_delivery' => 'E-mail',
                'document_email' => 'thana.seed@example.com',
                'loan_amount_type' => 'custom',
                'custom_loan_amount' => 120000,
            ],
            [
                'title' => 'อื่นๆ',
                'name' => 'กมลชนก พัฒน์',
                'name_en' => 'KAMONCHANOK PAT',
                'gender' => 'หญิง',
                'age' => 35,
                'income' => 36000,
                'occupation' => 'ที่ปรึกษาอิสระ',
                'has_other_debts' => 'มี',
                'other_debt_installment' => 4000,
                'dwelling_type' => 'อาศัยอยู่กับผู้อื่น: บ้านญาติ',
                'residence_status' => 'หอพักญาติ',
                'company_type' => 'สตูดิโอส่วนตัว',
            ],
            [
                'title' => 'นาย',
                'name' => 'ชัยวัฒน์ ทองแท้',
                'name_en' => 'CHAIWAT THONGTAE',
                'gender' => 'ชาย',
                'age' => 30,
                'income' => 28000,
                'has_other_debts' => 'ไม่มี',
                'other_debt_installment' => 0,
                'has_existing_loan' => 'ไม่ใช่',
                'residence_status' => 'เช่า/ผ่อนชำระ',
                'residence_rent_amount' => 8500,
                'average_monthly_income' => 5000,
                'business_income' => 'ทองแท้การช่าง',
            ],
            [
                'title' => 'นางสาว',
                'name' => 'ปิยะดา รุ่งเรือง',
                'name_en' => 'PIYADA RUNGRUEANG',
                'gender' => 'หญิง',
                'age' => 50,
                'marital_status' => 'สมรสไม่จดทะเบียน',
                'income' => 52000,
                'has_other_debts' => 'มี',
                'other_debt_installment' => 26000,
                'spouse_title' => 'นาย',
                'spouse_name' => 'อนุชา รุ่งเรือง',
                'spouse_phone' => '029876543',
                'spouse_mobile' => '0819876543',
                'spouse_education' => 'มัธยมปลาย',
                'spouse_occupation' => 'เจ้าของกิจการ',
                'spouse_company' => 'รุ่งเรืองโลจิสติกส์',
                'spouse_income' => 35000,
                'document_delivery' => 'ที่ทำงาน',
                'loan_amount_type' => 'full',
            ],
        ];
    }

    private function makeConsentFormPayload(array $attributes, int $sequence): array
    {
        $date = Carbon::create(2026, 6, min($sequence, 28));

        $payload = array_merge([
            'app_date' => $date->toDateString(),
            'app_no' => str_pad((string) $sequence, 13, '0', STR_PAD_LEFT),
            'officer_name' => 'เจ้าหน้าที่ทดสอบ ' . $sequence,
            'officer_phone' => '08100000' . str_pad((string) $sequence, 2, '0', STR_PAD_LEFT),
            'title' => 'นาย',
            'name' => 'ลูกค้าทดสอบ ' . $sequence,
            'name_en' => 'CUSTOMER TEST ' . $sequence,
            'dob' => $date->copy()->subYears(30)->toDateString(),
            'id_card' => '1101700000' . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT),
            'gender' => 'ชาย',
            'age' => 30,
            'nationality' => '',
            'marital_status' => 'โสด',
            'education' => 'ปริญญาตรี',
            'occupation' => 'พนักงานบริษัท',
            'income' => 30000,
            'extra_income' => 3000,
            'extra_income_source' => 'งานฟรีแลนซ์',
            'business_income' => 'กิจการตัวอย่าง',
            'average_monthly_income' => 7000,
            'has_other_debts' => 'ไม่มี',
            'other_debt_installment' => 0,
            'has_existing_loan' => null,
            'spouse_title' => null,
            'spouse_name' => null,
            'spouse_phone' => null,
            'spouse_mobile' => null,
            'spouse_education' => null,
            'spouse_occupation' => null,
            'spouse_company' => null,
            'spouse_income' => null,
            'dwelling_type' => 'บ้านเดี่ยว',
            'residence_status' => 'ปลอดภาระ',
            'residence_rent_amount' => null,
            'residence_years' => 5,
            'address_no' => (string) (100 + $sequence),
            'address_floor' => '1',
            'address_village' => (string) $sequence,
            'address_building' => 'หมู่บ้านตัวอย่าง',
            'address_soi' => 'สุขใจ ' . $sequence,
            'address_road' => 'มิตรภาพ',
            'address_subdistrict' => 'ในเมือง',
            'address_district' => 'เมืองตัวอย่าง',
            'address_province' => 'กรุงเทพมหานคร',
            'address_postal' => '1020' . (($sequence % 10) ?: 0),
            'phone_home' => '0211100' . str_pad((string) $sequence, 2, '0', STR_PAD_LEFT),
            'phone_mobile' => '0891111' . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT),
            'email' => 'customer' . $sequence . '@example.com',
            'line_id' => 'customer_line_' . $sequence,
            'use_home_address' => false,
            'company_type' => 'บจก.',
            'company_name' => 'บริษัทตัวอย่าง ' . $sequence,
            'business_type' => 'บริการ',
            'work_occupation' => 'พนักงานออฟฟิศ',
            'work_position' => 'เจ้าหน้าที่',
            'work_years' => 2,
            'work_months' => 6,
            'work_address_no' => '88/' . $sequence,
            'work_address_floor' => '2',
            'work_address_village' => '3',
            'work_address_building' => 'อาคารสำนักงาน',
            'work_address_soi' => 'รัชดา 4',
            'work_address_road' => 'รัชดาภิเษก',
            'work_address_subdistrict' => 'ดินแดง',
            'work_address_district' => 'ดินแดง',
            'work_address_province' => 'กรุงเทพมหานคร',
            'work_address_postal' => '10400',
            'work_phone' => '0212345' . str_pad((string) $sequence, 2, '0', STR_PAD_LEFT),
            'previous_company_name' => null,
            'previous_business_type' => null,
            'previous_position' => null,
            'previous_income' => null,
            'previous_work_years' => null,
            'previous_phone' => null,
            'document_delivery' => 'ที่อยู่ปัจจุบัน',
            'document_email' => null,
            'ref_name' => 'บุคคลอ้างอิง ' . $sequence,
            'ref_relation' => 'เพื่อน',
            'ref_address_no' => '9/' . $sequence,
            'ref_address_floor' => '1',
            'ref_address_village' => '2',
            'ref_address_building' => 'บ้านอ้างอิง',
            'ref_address_soi' => 'สัมพันธ์',
            'ref_address_road' => 'ประชาราษฎร์',
            'ref_address_subdistrict' => 'บางซื่อ',
            'ref_address_district' => 'บางซื่อ',
            'ref_address_province' => 'กรุงเทพมหานคร',
            'ref_address_postal' => '10800',
            'ref_phone_home' => '0222200' . str_pad((string) $sequence, 2, '0', STR_PAD_LEFT),
            'ref_phone_mobile' => '0862222' . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT),
            'ref_email' => 'ref' . $sequence . '@example.com',
            'ref_line_id' => 'ref_line_' . $sequence,
            'loan_term' => 24,
            'loan_amount_type' => 'full',
            'custom_loan_amount' => null,
            'loan_purpose' => 'ใช้เป็นเงินทุนหมุนเวียน',
            'bank_name' => 'ธนาคารกสิกรไทย',
            'bank_branch' => 'สำนักงานใหญ่',
            'account_name' => $attributes['name'] ?? ('ลูกค้าทดสอบ ' . $sequence),
            'account_type' => 'ออมทรัพย์',
            'account_number' => '123456789' . $sequence,
            'signed' => true,
            'signed_at' => $date->copy()->setTime(10, 30),
            'signature_data' => $this->buildSignatureData($sequence),
            'status' => 'pending',
        ], $attributes);

        if ($payload['use_home_address']) {
            $payload['work_address_no'] = $payload['address_no'];
            $payload['work_address_floor'] = $payload['address_floor'];
            $payload['work_address_village'] = $payload['address_village'];
            $payload['work_address_building'] = $payload['address_building'];
            $payload['work_address_soi'] = $payload['address_soi'];
            $payload['work_address_road'] = $payload['address_road'];
            $payload['work_address_subdistrict'] = $payload['address_subdistrict'];
            $payload['work_address_district'] = $payload['address_district'];
            $payload['work_address_province'] = $payload['address_province'];
            $payload['work_address_postal'] = $payload['address_postal'];
        }

        $payload['status'] = $this->determineStatus($payload);

        return $payload;
    }

    private function buildSignatureData(int $sequence): string
    {
        $x = 20 + ($sequence % 10);
        $y = 30 + ($sequence % 8);

        return json_encode([
            [
                'points' => [
                    ['x' => $x, 'y' => $y, 'time' => 0],
                    ['x' => $x + 60, 'y' => $y + 5, 'time' => 40],
                    ['x' => $x + 120, 'y' => $y - 8, 'time' => 80],
                ],
                'pressure' => 0.5,
                'penColor' => 'rgb(0, 0, 0)',
                'color' => 'rgb(0, 0, 0)',
                'compositeOperation' => 'source-over',
            ],
        ], JSON_UNESCAPED_UNICODE);
    }

    private function determineStatus(array $payload): string
    {
        $age = (int) ($payload['age'] ?? 0);
        $income = (float) ($payload['income'] ?? 0);
        $otherDebtInstallment = (float) ($payload['other_debt_installment'] ?? 0);

        if ($age < 20 || $age > 50) {
            return 'rejected';
        }

        if ($income < 15000) {
            return 'rejected';
        }

        if ($income > 0 && $otherDebtInstallment > ($income / 2)) {
            return 'rejected';
        }

        return 'approved';
    }
}
