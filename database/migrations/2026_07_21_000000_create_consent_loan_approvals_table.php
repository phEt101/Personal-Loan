<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consent_loan_approvals', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key');
            $table->foreignId('application_id')->comment('อ้างอิงใบคำขอ (consent_requests)')->constrained('consent_requests')->cascadeOnDelete()->unique();
            $table->decimal('interest_rate', 10, 2)->nullable()->comment('ดอกเบี้ยเงินกู้ (% ต่อปี)');
            $table->decimal('fee_rate', 10, 2)->nullable()->comment('ค่าธรรมเนียม (% ต่อปี)');
            $table->decimal('loan_amount', 12, 2)->nullable()->comment('วงเงินสินเชื่อ');
            $table->decimal('late_penalty_rate', 10, 2)->nullable()->comment('เบี้ยปรับล่าช้า (% ต่อปี)');
            $table->integer('installments')->nullable()->comment('จำนวนงวดการผ่อน');
            $table->decimal('monthly_payment', 12, 2)->nullable()->comment('ค่างวดต่อเดือน (หลังปัดเศษ)');
            $table->decimal('monthly_payment_raw', 12, 2)->nullable()->comment('ค่างวดต่อเดือน (ก่อนปัดเศษ)');
            $table->decimal('total_interest', 12, 2)->nullable()->comment('ดอกเบี้ยเงินกู้ทั้งสัญญา');
            $table->decimal('total_contract_amount', 12, 2)->nullable()->comment('มูลค่าสัญญาเงินกู้');
            $table->timestamp('created_at')->nullable()->comment('วันที่เวลาสร้างข้อมูล');
            $table->timestamp('updated_at')->nullable()->comment('วันที่เวลาแก้ไขล่าสุด');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consent_loan_approvals');
    }
};
