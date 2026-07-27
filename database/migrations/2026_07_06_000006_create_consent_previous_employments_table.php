<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consent_previous_employments', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key');
            $table->foreignId('applicant_id')->comment('อ้างอิงผู้ขอ (consent_request_applicants)')->constrained('consent_request_applicants')->cascadeOnDelete()->unique();
            $table->foreignId('reference_id')->nullable()->comment('อ้างอิงผู้รับรอง (consent_references)')->constrained('consent_references')->nullOnDelete();
            $table->string('previous_company_name', 100)->nullable()->comment('ชื่อสถานที่ทำงานเดิม');
            $table->string('previous_position', 100)->nullable()->comment('ตำแหน่งเดิม');
            $table->decimal('previous_income', 12, 2)->nullable()->comment('รายได้เดิม');
            $table->text('previous_address')->nullable()->comment('ที่อยู่สถานที่ทำงานเดิม');
            $table->string('previous_phone', 20)->nullable()->comment('เบอร์โทรที่ทำงานเดิม');
            $table->timestamp('created_at')->nullable()->comment('วันที่เวลาสร้างข้อมูล');
            $table->timestamp('updated_at')->nullable()->comment('วันที่เวลาแก้ไขล่าสุด');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consent_previous_employments');
    }
};
