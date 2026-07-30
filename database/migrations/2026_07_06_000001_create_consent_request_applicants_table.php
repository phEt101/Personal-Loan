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
            $table->foreignId('application_id')->comment('อ้างอิงใบคำขอ (consent_requests)')->constrained('consent_requests')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->comment('อ้างอิงลูกค้า (customers)')->constrained('customers')->nullOnDelete();
            $table->string('title', 50)->nullable()->comment('คำนำหน้า');
            $table->string('name', 150)->nullable()->comment('ชื่อ-นามสกุล');
            $table->string('name_en', 150)->nullable()->comment('ชื่อ-นามสกุล (อังกฤษ)');
            $table->date('birthdate')->nullable()->comment('วันเดือนปีเกิด');
            $table->string('id_card', 20)->nullable()->comment('เลขบัตรประชาชน');
            $table->string('passport', 20)->nullable()->comment('เลขหนังสือเดินทาง');
            $table->string('nationality', 50)->nullable()->comment('สัญชาติ');
            $table->string('marital_status', 50)->nullable()->comment('สถานภาพสมรส');
            $table->string('education', 50)->nullable()->comment('ระดับการศึกษา');
            $table->string('education_other', 100)->nullable()->comment('ระบุการศึกษาอื่นๆ');
            $table->string('occupation', 100)->nullable()->comment('อาชีพ');
            $table->string('government_level', 100)->nullable()->comment('ระดับข้าราชการ');
            $table->string('occupation_other', 100)->nullable()->comment('ระบุอาชีพอื่นๆ');
            $table->string('career_field', 100)->nullable()->comment('สาขาอาชีพ');
            $table->string('career_field_other', 100)->nullable()->comment('ระบุสาขาอาชีพอื่นๆ');
            $table->decimal('income', 12, 2)->nullable()->comment('รายได้หลัก');
            $table->decimal('extra_income', 12, 2)->nullable()->comment('รายได้เสริม');
            $table->string('extra_income_source', 100)->nullable()->comment('แหล่งที่มาของรายได้');
            $table->string('extra_income_source_other', 255)->nullable()->comment('ระบุแหล่งที่มาของรายได้อื่นๆ');
            $table->string('income_country', 100)->nullable()->comment('ประเทศที่มาของรายได้');
            $table->boolean('has_other_debts')->nullable()->comment('มีหนี้สินอื่นหรือไม่');
            $table->decimal('other_debt_installment', 12, 2)->nullable()->comment('ค่างวดหนี้สินอื่นต่อเดือน');
            $table->boolean('has_existing_loan')->nullable()->comment('มีสินเชื่อ/กู้ยืมเดิมหรือไม่');
            $table->unsignedInteger('existing_loan_institution_count')->nullable()->comment('จำนวนสถาบัน/ผู้ประกอบธุรกิจที่มีวงเงินสินเชื่อรวม');
            $table->decimal('existing_loan_total_amount', 12, 2)->nullable()->comment('วงเงินสินเชื่อส่วนบุคคลรวมทั้งสิ้น');
            $table->timestamp('created_at')->nullable()->comment('วันที่เวลาสร้างข้อมูล');
            $table->timestamp('updated_at')->nullable()->comment('วันที่เวลาแก้ไขล่าสุด');

            $table->unsignedInteger('applicant_order')->default(1)->comment('ลำดับผู้ขอกู้ในสัญญา');

            $table->index('application_id');
            $table->index('customer_id');
            $table->index(['application_id', 'applicant_order'], 'consent_applicants_app_order_idx');
            $table->index('id_card');
            $table->index('passport');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consent_request_applicants');
    }
};
