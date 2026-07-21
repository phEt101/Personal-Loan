<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Consent\Models\LoanProduct;

class LoanProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name_th' => 'สินเชื่อส่วนบุคคลไม่มีทรัพย์ทั่วไป',
                'name_en' => 'Personal Loan (P-Loan)',
                'bot_code' => '101',
                'interest_rate_cap' => 25.00,
                'fee_rate' => 0.05,
                'max_loan_term' => 60,
                'max_loan_amount' => null, // ไม่จำกัดวงเงินก้อนใหญ่ แต่จำกัดที่ตัวคูณรายได้
                'income_threshold' => 30000.00,
                'multiplier_low_income' => 1.5,
                'multiplier_high_income' => 5.0,
            ],
            [
                'name_th' => 'สินเชื่อนาโนไฟแนนซ์',
                'name_en' => 'Nano Finance',
                'bot_code' => '201',
                'interest_rate_cap' => 33.00,
                'fee_rate' => 0.05,
                'max_loan_term' => 24,
                'max_loan_amount' => 100000.00,
                'income_threshold' => null,
                'multiplier_low_income' => null,
                'multiplier_high_income' => null,
            ],
        ];

        foreach ($products as $product) {
            LoanProduct::create($product);
        }
    }
}
