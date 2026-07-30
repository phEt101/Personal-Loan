<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consent_employments', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key');
            $table->foreignId('applicant_id')->comment('อ้างอิงผู้ขอ (consent_request_applicants)')->constrained('consent_request_applicants')->cascadeOnDelete()->unique();
            $table->foreignId('reference_id')->nullable()->comment('อ้างอิงผู้รับรอง (consent_references)')->constrained('consent_references')->nullOnDelete();
            $table->boolean('use_home_address')->default(false)->comment('ใช้ที่อยู่บ้านเป็นที่อยู่ที่ทำงานหรือไม่');
            $table->string('company_name', 100)->nullable()->comment('ชื่อบริษัท/หน่วยงาน');
            $table->string('business_type', 100)->nullable()->comment('ประเภทธุรกิจ');
            $table->string('business_type_other', 255)->nullable()->comment('ประเภทธุรกิจอื่นๆ');
            $table->string('work_department', 100)->nullable()->comment('แผนก/ฝ่าย');
            $table->integer('work_years')->nullable()->comment('อายุงาน (ปี)');
            $table->integer('work_months')->nullable()->comment('อายุงาน (เดือน)');
            $table->string('work_phone', 20)->nullable()->comment('เบอร์โทรที่ทำงาน');
            $table->timestamp('created_at')->nullable()->comment('วันที่เวลาสร้างข้อมูล');
            $table->timestamp('updated_at')->nullable()->comment('วันที่เวลาแก้ไขล่าสุด');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consent_employments');
    }
};

