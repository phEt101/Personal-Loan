<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consent_spouses', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key');
            $table->foreignId('application_id')->comment('อ้างอิงใบคำขอ (consent_requests)')->constrained('consent_requests')->cascadeOnDelete()->unique();
            $table->string('spouse_title', 50)->nullable()->comment('คำนำหน้าคู่สมรส');
            $table->string('spouse_name', 150)->nullable()->comment('ชื่อ-นามสกุลคู่สมรส');
            $table->string('spouse_phone', 20)->nullable()->comment('เบอร์โทรบ้านคู่สมรส');
            $table->string('spouse_mobile', 20)->nullable()->comment('เบอร์มือถือคู่สมรส');
            $table->string('spouse_education', 50)->nullable()->comment('ระดับการศึกษาคู่สมรส');
            $table->string('spouse_occupation', 100)->nullable()->comment('อาชีพคู่สมรส');
            $table->string('spouse_company', 100)->nullable()->comment('ชื่อสถานที่ทำงาน/บริษัทคู่สมรส');
            $table->decimal('spouse_income', 12, 2)->nullable()->comment('รายได้คู่สมรส');
            $table->timestamp('created_at')->nullable()->comment('วันที่เวลาสร้างข้อมูล');
            $table->timestamp('updated_at')->nullable()->comment('วันที่เวลาแก้ไขล่าสุด');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consent_spouses');
    }
};
