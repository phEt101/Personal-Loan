<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consent_request_applicants', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key');
            $table->foreignId('application_id')->comment('อ้างอิงใบคำขอ (consent_requests)')->constrained('consent_requests')->cascadeOnDelete()->unique();
            $table->string('title', 50)->nullable()->comment('คำนำหน้า');
            $table->string('name', 150)->comment('ชื่อ-นามสกุล');
            $table->string('name_en', 150)->nullable()->comment('ชื่อ-นามสกุล (อังกฤษ)');
            $table->date('dob')->nullable()->comment('วันเดือนปีเกิด');
            $table->string('id_card', 20)->nullable()->comment('เลขบัตรประชาชน');
            $table->string('gender', 10)->nullable()->comment('เพศ');
            $table->integer('age')->nullable()->comment('อายุ');
            $table->string('nationality', 50)->nullable()->comment('สัญชาติ');
            $table->string('marital_status', 50)->nullable()->comment('สถานภาพสมรส');
            $table->string('education', 50)->nullable()->comment('ระดับการศึกษา');
            $table->string('occupation', 100)->nullable()->comment('อาชีพ');
            $table->decimal('income', 12, 2)->nullable()->comment('รายได้หลัก');
            $table->decimal('extra_income', 12, 2)->nullable()->comment('รายได้เสริม');
            $table->string('extra_income_source', 100)->nullable()->comment('แหล่งที่มารายได้เสริม');
            $table->string('business_income', 255)->nullable()->comment('รายได้จากธุรกิจ/กิจการ');
            $table->decimal('average_monthly_income', 12, 2)->nullable()->comment('รายได้เฉลี่ยต่อเดือน');
            $table->boolean('has_other_debts')->nullable()->comment('มีหนี้สินอื่นหรือไม่');
            $table->decimal('other_debt_installment', 12, 2)->nullable()->comment('ค่างวดหนี้สินอื่นต่อเดือน');
            $table->boolean('has_existing_loan')->nullable()->comment('มีสินเชื่อ/กู้ยืมเดิมหรือไม่');
            $table->timestamp('created_at')->nullable()->comment('วันที่เวลาสร้างข้อมูล');
            $table->timestamp('updated_at')->nullable()->comment('วันที่เวลาแก้ไขล่าสุด');

            $table->index('id_card');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consent_request_applicants');
    }
};
