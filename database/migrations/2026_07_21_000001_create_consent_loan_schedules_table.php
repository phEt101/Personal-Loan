<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consent_loan_schedules', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key');
            $table->foreignId('application_id')->comment('อ้างอิงใบคำขอ (consent_requests)')->constrained('consent_requests')->cascadeOnDelete();
            $table->integer('installment_no')->comment('งวดที่');
            $table->date('due_date')->nullable()->comment('วันครบกำหนดชำระ');
            $table->decimal('payment_amount', 12, 2)->nullable()->comment('เงินค่างวด');
            $table->decimal('principal_amount', 12, 2)->nullable()->comment('เงินต้น');
            $table->decimal('interest_amount', 12, 2)->nullable()->comment('ดอกเบี้ย');
            $table->decimal('fee_amount', 12, 2)->nullable()->comment('ค่าธรรมเนียม');
            $table->decimal('remaining_principal', 12, 2)->nullable()->comment('เงินต้นคงเหลือ');
            $table->timestamp('created_at')->nullable()->comment('วันที่เวลาสร้างข้อมูล');
            $table->timestamp('updated_at')->nullable()->comment('วันที่เวลาแก้ไขล่าสุด');

            $table->index(['application_id', 'installment_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consent_loan_schedules');
    }
};
