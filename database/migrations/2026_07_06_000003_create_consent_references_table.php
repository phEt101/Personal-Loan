<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consent_references', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key');
            $table->foreignId('applicant_id')->comment('อ้างอิงผู้ขอ (consent_request_applicants)')->constrained('consent_request_applicants')->cascadeOnDelete()->unique();
            $table->enum('ref_type', ['reference', 'guarantor'])->default('reference')->comment('ประเภทผู้รับรอง');
            $table->string('ref_name', 150)->nullable()->comment('ชื่อ-นามสกุลผู้รับรอง');
            $table->date('birthdate')->nullable()->comment('วันเดือนปีเกิด');
            $table->string('ref_relation', 100)->nullable()->comment('ความสัมพันธ์กับผู้กู้');
            $table->string('ref_phone_home', 20)->nullable()->comment('เบอร์โทรบ้านผู้รับรอง');
            $table->string('ref_phone_mobile', 20)->nullable()->comment('เบอร์มือถือผู้รับรอง');
            $table->string('nationality', 50)->nullable()->comment('สัญชาติ');
            $table->string('marital_status', 50)->nullable()->comment('สถานภาพสมรส');
            $table->string('education', 50)->nullable()->comment('ระดับการศึกษา');
            $table->string('occupation', 100)->nullable()->comment('อาชีพ');
            $table->string('government_level', 100)->nullable()->comment('ระดับข้าราชการ');
            $table->string('occupation_other', 100)->nullable()->comment('ระบุอาชีพอื่นๆ');
            $table->string('career_field', 100)->nullable()->comment('สาขาอาชีพ');
            $table->string('career_field_other', 100)->nullable()->comment('ระบุสาขาอาชีพอื่นๆ');
            $table->decimal('income', 12, 2)->nullable()->comment('รายได้หลัก');
            $table->decimal('extra_income', 12, 2)->nullable()->comment('รายได้เสริม');
            $table->string('extra_income_source', 100)->nullable()->comment('แหล่งที่มาของรายได้');
            $table->string('income_country', 100)->nullable()->comment('ประเทศที่มาของรายได้');
            $table->boolean('has_other_debts')->nullable()->comment('มีหนี้สินอื่นหรือไม่');
            $table->decimal('other_debt_installment', 12, 2)->nullable()->comment('ค่างวดหนี้สินอื่นต่อเดือน');
            $table->boolean('has_existing_loan')->nullable()->comment('มีสินเชื่อ/กู้ยืมเดิมหรือไม่');
            $table->unsignedInteger('existing_loan_institution_count')->nullable()->comment('จำนวนสถาบัน/ผู้ประกอบธุรกิจที่มีวงเงินสินเชื่อรวม');
            $table->decimal('existing_loan_total_amount', 12, 2)->nullable()->comment('วงเงินสินเชื่อส่วนบุคคลรวมทั้งสิ้น');
            $table->timestamp('created_at')->nullable()->comment('วันที่เวลาสร้างข้อมูล');
            $table->timestamp('updated_at')->nullable()->comment('วันที่เวลาแก้ไขล่าสุด');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consent_references');
    }
};
