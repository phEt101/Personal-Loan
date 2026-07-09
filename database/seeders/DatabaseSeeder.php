<?php

namespace Database\Seeders;

use App\Modules\Consent\Models\ConsentAddress;
use App\Modules\Consent\Models\ConsentApplicant;
use App\Modules\Consent\Models\ConsentApplication;
use App\Modules\Consent\Models\ConsentContact;
use App\Modules\Consent\Models\ConsentDisbursementAccount;
use App\Modules\Consent\Models\ConsentEmployment;
use App\Modules\Consent\Models\ConsentLoanRequest;
use App\Modules\Consent\Models\ConsentPreviousEmployment;
use App\Modules\Consent\Models\ConsentReference;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

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

        foreach ($this->buildConsentForms() as $index => $attributes) {
            $payload = $this->makeConsentFormPayload($attributes, $index + 1);
            $this->seedConsentApplication($payload);
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
                'birthdate' => '1992-11-18',
                'id_card' => '1101701234567',
                'nationality' => 'ไทย',
                'marital_status' => 'โสด',
                'education' => 'ปริญญาตรี',
                'occupation' => 'พนักงานบริษัท',
                'income' => 38000,
                'extra_income' => 5000,
                'extra_income_source' => 'ค่าคอมมมิชั่น',
                'has_other_debts' => 'มี',
                'other_debt_installment' => 12000,
                'residence_status' => 'เช่าอยู่',
                'address_room' => '1201',
                'address_no' => '88/12',
                'address_floor' => '12',
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
                'use_home_address' => true,
                'company_name' => 'บริษัท โซลูชั่น จำกัด',
                'business_type' => 'บริการ',
                'work_department' => 'ฝ่ายไอที',
                'work_years' => 4,
                'work_months' => 2,
                'document_delivery' => 'ประสงค์รับทางอีเมล',
                'loan_term' => 24,
                'loan_amount_type' => 'custom',
                'custom_loan_amount' => 120000,
                'loan_purpose' => 'ปิดหนี้และรวมหนี้',
                'bank_name' => 'ธนาคารกสิกรไทย',
                'account_name' => 'ณัฐพงศ์ วัฒนศิริ',
                'account_type' => 'ออมทรัพย์',
                'account_number' => '123456789012',
                'payment_method' => 'ชําระโดยการหักบัญชี',
                'direct_debit_amount' => 5000,
                'direct_debit_account_number' => '123456789012',
            ],
            [
                'title' => 'นางสาว',
                'name' => 'พรทิพย์ สดใส',
                'name_en' => 'PORNTHIP SODSAI',
                'birthdate' => '2007-01-01',
                'income' => 21000,
                'has_other_debts' => 'ไม่มี',
                'other_debt_installment' => 0,
            ],
            [
                'title' => 'นาย',
                'name' => 'วิชัย เกษม',
                'name_en' => 'WICHAI KASEM',
                'birthdate' => '1975-01-01',
                'income' => 27000,
                'has_other_debts' => 'มี',
                'other_debt_installment' => 7000,
            ],
            [
                'title' => 'นาง',
                'name' => 'ศิริพร เงินดี',
                'name_en' => 'SIRIPORN NGERNDEE',
                'birthdate' => '1998-01-01',
                'income' => 14999,
                'has_other_debts' => 'ไม่มี',
                'other_debt_installment' => 0,
                'has_existing_loan' => 'ใช่',
                'existing_loan_institution_count' => 3,
                'existing_loan_total_amount' => 250000,
                'document_delivery' => 'ประสงค์รับทางอีเมล',
            ],
            [
                'title' => 'นาย',
                'name' => 'ประยูร ภูผา',
                'name_en' => 'PRAYOON PUPHA',
                'birthdate' => '1986-01-01',
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
                'birthdate' => '1988-01-01',
                'marital_status' => 'สมรส',
                'income' => 45000,
                'has_other_debts' => 'มี',
                'other_debt_installment' => 9000,
            ],
            [
                'title' => 'นาย',
                'name' => 'ธนา วิริยะ',
                'name_en' => 'THANA WIRIYA',
                'id_card' => null,
                'passport' => 'AA1234567',
                'birthdate' => '2006-01-01',
                'income' => 15000,
                'has_other_debts' => 'ไม่มี',
                'other_debt_installment' => 0,
                'use_home_address' => true,
                'work_years' => 0,
                'work_months' => 8,
                'previous_company_name' => 'Old Growth Ltd.',
                'previous_position' => 'พนักงานขาย',
                'previous_income' => 22000,
                'previous_address' => '88 Old Road, Retail District, Bangkok 10110',
                'previous_phone' => '026661234',
                'document_delivery' => 'ประสงค์รับทางอีเมล',
                'birth_place_address' => '123 Main Street, New York, NY 10001, USA',
                'loan_amount_type' => 'custom',
                'custom_loan_amount' => 120000,
            ],
            [
                'title' => 'อื่นๆ',
                'name' => 'กมลชนก พัฒน์',
                'name_en' => 'KAMONCHANOK PAT',
                'birthdate' => '1991-01-01',
                'income' => 36000,
                'occupation' => 'อาชีพอิสระ',
                'occupation_other' => 'ที่ปรึกษาอิสระ',
                'career_field' => 'อื่นๆ',
                'career_field_other' => 'ที่ปรึกษาด้านธุรกิจ',
                'has_other_debts' => 'มี',
                'other_debt_installment' => 4000,
                'residence_status' => 'บ้านญาติ/พี่น้อง/บุคคลอื่น',
            ],
            [
                'title' => 'นาย',
                'name' => 'ชัยวัฒน์ ทองแท้',
                'name_en' => 'CHAIWAT THONGTAE',
                'birthdate' => '1996-01-01',
                'income' => 28000,
                'has_other_debts' => 'ไม่มี',
                'other_debt_installment' => 0,
                'has_existing_loan' => 'ไม่ใช่',
                'residence_status' => 'เช่าอยู่',
                'income_country' => 'ประเทศไทย',
            ],
            [
                'title' => 'นางสาว',
                'name' => 'ปิยะดา รุ่งเรือง',
                'name_en' => 'PIYADA RUNGRUEANG',
                'birthdate' => '1976-01-01',
                'marital_status' => 'สมรสไม่จดทะเบียน',
                'income' => 52000,
                'has_other_debts' => 'มี',
                'other_debt_installment' => 26000,
                'document_delivery' => 'ประสงค์รับทางไปรษณีย์',
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
            'birthdate' => $date->copy()->subYears(30)->toDateString(),
            'id_card' => '1101700000' . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT),
            'nationality' => '',
            'marital_status' => 'โสด',
            'education' => 'ปริญญาตรี',
            'occupation' => 'พนักงานบริษัท',
            'government_level' => null,
            'occupation_other' => null,
            'career_field' => 'พนักงานขาย',
            'career_field_other' => null,
            'income' => 30000,
            'extra_income' => 3000,
            'extra_income_source' => 'โบนัส',
            'income_country' => 'ประเทศไทย',
            'has_other_debts' => 'ไม่มี',
            'other_debt_installment' => 0,
            'has_existing_loan' => null,
            'existing_loan_institution_count' => null,
            'existing_loan_total_amount' => null,
            'residence_status' => 'บ้านตนเองปลอดภาระ',
            'address_room' => null,
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
            'line_id' => null,
            'use_home_address' => false,
            'company_name' => 'บริษัทตัวอย่าง ' . $sequence,
            'business_type' => 'บริการ',
            'work_department' => 'ฝ่ายขาย',
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
            'document_address_text' => null,
            'document_address_province' => null,
            'document_address_postal' => null,
            'birth_place_address' => null,
            'previous_company_name' => null,
            'previous_position' => null,
            'previous_income' => null,
            'previous_address' => null,
            'previous_phone' => null,
            'document_delivery' => 'บริการแจ้งเตือนผ่าน SMS',
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
            'loan_term' => 24,
            'loan_amount_type' => 'full',
            'custom_loan_amount' => null,
            'loan_purpose' => 'ใช้เป็นเงินทุนหมุนเวียน',
            'bank_name' => 'ธนาคารกสิกรไทย',
            'account_name' => $attributes['name'] ?? ('ลูกค้าทดสอบ ' . $sequence),
            'account_type' => 'ออมทรัพย์',
            'account_number' => '123456789' . $sequence,
            'payment_method' => 'ชําระด้วยเงินสด',
            'direct_debit_amount' => null,
            'direct_debit_account_number' => null,
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
        // Calculate age from birthdate
        $age = 0;
        if (!empty($payload['birthdate'])) {
            $age = Carbon::parse($payload['birthdate'])->age;
        }
        
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

    private function seedConsentApplication(array $payload): void
    {
        if (!Schema::hasTable('consent_requests')) {
            return;
        }

        DB::transaction(function () use ($payload) {
            $application = ConsentApplication::create([
                'app_date' => $payload['app_date'] ?? null,
                'app_no' => $payload['app_no'] ?? null,
                'officer_name' => $payload['officer_name'] ?? null,
                'officer_phone' => $payload['officer_phone'] ?? null,
                'document_delivery' => $payload['document_delivery'] ?? null,
                'status' => $payload['status'] ?? 'pending',
                'signed' => (bool) ($payload['signed'] ?? false),
                'signed_at' => $payload['signed_at'] ?? null,
                'signature_data' => $payload['signature_data'] ?? null,
            ]);

            if (!$application->encrypted_id) {
                $application->encrypted_id = ConsentApplication::makeEncryptedId((string) $application->id);
                $application->saveQuietly();
            }

            $hasOtherDebtsRaw = $payload['has_other_debts'] ?? null;
            $hasOtherDebts = $hasOtherDebtsRaw === null ? null : ($hasOtherDebtsRaw === 'มี');

            $hasExistingLoanRaw = $payload['has_existing_loan'] ?? null;
            $hasExistingLoan = $hasExistingLoanRaw === null ? null : ($hasExistingLoanRaw === 'ใช่');

            ConsentApplicant::create([
                'application_id' => $application->id,
                'title' => $payload['title'] ?? null,
                'name' => $payload['name'] ?? '-',
                'name_en' => $payload['name_en'] ?? null,
                'birthdate' => $payload['birthdate'] ?? null,
                'id_card' => $payload['id_card'] ?? null,
                'passport' => $payload['passport'] ?? null,
                'nationality' => $payload['nationality'] ?? null,
                'marital_status' => $payload['marital_status'] ?? null,
                'education' => $payload['education'] ?? null,
                'occupation' => $payload['occupation'] ?? null,
                'government_level' => $payload['government_level'] ?? null,
                'occupation_other' => $payload['occupation_other'] ?? null,
                'career_field' => $payload['career_field'] ?? null,
                'career_field_other' => $payload['career_field_other'] ?? null,
                'income' => $payload['income'] ?? null,
                'extra_income' => $payload['extra_income'] ?? null,
                'extra_income_source' => $payload['extra_income_source'] ?? null,
                'income_country' => $payload['income_country'] ?? null,
                'has_other_debts' => $hasOtherDebts,
                'other_debt_installment' => $payload['other_debt_installment'] ?? null,
                'has_existing_loan' => $hasExistingLoan,
                'existing_loan_institution_count' => $payload['existing_loan_institution_count'] ?? null,
                'existing_loan_total_amount' => $payload['existing_loan_total_amount'] ?? null,
            ]);

            ConsentContact::create([
                'application_id' => $application->id,
                'phone_home' => $payload['phone_home'] ?? null,
                'phone_mobile' => $payload['phone_mobile'] ?? null,
                'email' => $payload['email'] ?? null,
            ]);

            ConsentAddress::create([
                'application_id' => $application->id,
                'kind' => 'home',
                'residence_status' => $payload['residence_status'] ?? null,
                'address_room' => $payload['address_room'] ?? null,
                'address_no' => $payload['address_no'] ?? null,
                'address_floor' => $payload['address_floor'] ?? null,
                'address_village' => $payload['address_village'] ?? null,
                'address_building' => $payload['address_building'] ?? null,
                'address_soi' => $payload['address_soi'] ?? null,
                'address_road' => $payload['address_road'] ?? null,
                'address_subdistrict' => $payload['address_subdistrict'] ?? null,
                'address_district' => $payload['address_district'] ?? null,
                'address_province' => $payload['address_province'] ?? null,
                'address_postal' => $payload['address_postal'] ?? null,
            ]);

            ConsentEmployment::create([
                'application_id' => $application->id,
                'use_home_address' => (bool) ($payload['use_home_address'] ?? false),
                'company_name' => $payload['company_name'] ?? null,
                'business_type' => $payload['business_type'] ?? null,
                'work_department' => $payload['work_department'] ?? null,
                'work_years' => $payload['work_years'] ?? null,
                'work_months' => $payload['work_months'] ?? null,
                'work_phone' => $payload['work_phone'] ?? null,
            ]);

            ConsentAddress::create([
                'application_id' => $application->id,
                'kind' => 'work',
                'address_no' => $payload['work_address_no'] ?? null,
                'address_floor' => $payload['work_address_floor'] ?? null,
                'address_village' => $payload['work_address_village'] ?? null,
                'address_building' => $payload['work_address_building'] ?? null,
                'address_soi' => $payload['work_address_soi'] ?? null,
                'address_road' => $payload['work_address_road'] ?? null,
                'address_subdistrict' => $payload['work_address_subdistrict'] ?? null,
                'address_district' => $payload['work_address_district'] ?? null,
                'address_province' => $payload['work_address_province'] ?? null,
                'address_postal' => $payload['work_address_postal'] ?? null,
            ]);

            ConsentAddress::create([
                'application_id' => $application->id,
                'kind' => 'document',
                'address_text' => $payload['document_address_text'] ?? null,
                'address_province' => $payload['document_address_province'] ?? null,
                'address_postal' => $payload['document_address_postal'] ?? null,
                'birth_place_address' => $payload['birth_place_address'] ?? null,
            ]);

            ConsentPreviousEmployment::create([
                'application_id' => $application->id,
                'previous_company_name' => $payload['previous_company_name'] ?? null,
                'previous_position' => $payload['previous_position'] ?? null,
                'previous_income' => $payload['previous_income'] ?? null,
                'previous_address' => $payload['previous_address'] ?? null,
                'previous_phone' => $payload['previous_phone'] ?? null,
            ]);

            ConsentReference::create([
                'application_id' => $application->id,
                'ref_name' => $payload['ref_name'] ?? null,
                'ref_relation' => $payload['ref_relation'] ?? null,
                'ref_phone_home' => $payload['ref_phone_home'] ?? null,
                'ref_phone_mobile' => $payload['ref_phone_mobile'] ?? null,
            ]);

            ConsentAddress::create([
                'application_id' => $application->id,
                'kind' => 'reference',
                'address_no' => $payload['ref_address_no'] ?? null,
                'address_floor' => $payload['ref_address_floor'] ?? null,
                'address_village' => $payload['ref_address_village'] ?? null,
                'address_building' => $payload['ref_address_building'] ?? null,
                'address_soi' => $payload['ref_address_soi'] ?? null,
                'address_road' => $payload['ref_address_road'] ?? null,
                'address_subdistrict' => $payload['ref_address_subdistrict'] ?? null,
                'address_district' => $payload['ref_address_district'] ?? null,
                'address_province' => $payload['ref_address_province'] ?? null,
                'address_postal' => $payload['ref_address_postal'] ?? null,
            ]);

            ConsentLoanRequest::create([
                'application_id' => $application->id,
                'loan_term' => $payload['loan_term'] ?? null,
                'loan_amount_type' => $payload['loan_amount_type'] ?? null,
                'custom_loan_amount' => $payload['custom_loan_amount'] ?? null,
                'loan_purpose' => $payload['loan_purpose'] ?? null,
            ]);

            ConsentDisbursementAccount::create([
                'application_id' => $application->id,
                'bank_name' => $payload['bank_name'] ?? null,
                'account_name' => $payload['account_name'] ?? null,
                'account_type' => $payload['account_type'] ?? null,
                'account_number' => $payload['account_number'] ?? null,
                'payment_method' => $payload['payment_method'] ?? null,
                'direct_debit_amount' => $payload['direct_debit_amount'] ?? null,
                'direct_debit_account_number' => $payload['direct_debit_account_number'] ?? null,
            ]);
        });
    }
}
