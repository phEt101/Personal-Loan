<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_products', function (Blueprint $table) {
            $table->id();
            $table->string('name_th', 100);
            $table->string('name_en', 100);
            $table->string('bot_code', 20)->nullable()->comment('รหัสประเภทสินเชื่อตาม BOT');
            $table->decimal('interest_rate_cap', 10, 2)->default(25.00)->comment('เพดานดอกเบี้ยสูงสุด (% ต่อปี)');
            $table->decimal('fee_rate', 10, 4)->default(0.05)->comment('อัตราค่าธรรมเนียม (%)');
            $table->decimal('late_fee', 8, 2)->default(3.00)->comment('เบี้ยปรับล่าช้า (% ต่อปี)');
            $table->integer('max_loan_term')->default(60)->comment('ระยะเวลาผ่อนชำระสูงสุด (เดือน)');
            $table->decimal('max_loan_amount', 12, 2)->nullable()->comment('วงเงินอนุมัติสูงสุด (บาท)');
            $table->decimal('income_threshold', 12, 2)->nullable()->default(30000.00)->comment('เกณฑ์รายได้ในการแบ่งตัวคูณ (บาท)');
            $table->decimal('multiplier_low_income', 4, 2)->nullable()->comment('ตัวคูณรายได้กรณีรายได้น้อยกว่าเกณฑ์');
            $table->decimal('multiplier_high_income', 4, 2)->nullable()->comment('ตัวคูณรายได้กรณีรายได้มากกว่าเกณฑ์');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_products');
    }
};
