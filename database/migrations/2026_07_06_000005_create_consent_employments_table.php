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
            $table->foreignId('application_id')->comment('อ้างอิงใบคำขอ (consent_requests)')->constrained('consent_requests')->cascadeOnDelete()->unique();
            $table->boolean('use_home_address')->default(false)->comment('ใช้ที่อยู่บ้านเป็นที่อยู่ที่ทำงานหรือไม่');
            $table->string('company_type', 100)->nullable()->comment('ประเภทบริษัท/หน่วยงาน');
            $table->string('company_name', 100)->nullable()->comment('ชื่อบริษัท/หน่วยงาน');
            $table->string('business_type', 100)->nullable()->comment('ประเภทธุรกิจ');
            $table->string('work_occupation', 100)->nullable()->comment('ลักษณะงาน/อาชีพ');
            $table->string('work_position', 100)->nullable()->comment('ตำแหน่งงาน');
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

