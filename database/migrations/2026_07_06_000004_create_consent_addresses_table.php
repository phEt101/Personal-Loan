<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consent_addresses', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key');
            $table->foreignId('application_id')->comment('อ้างอิงใบคำขอ (consent_requests)')->constrained('consent_requests')->cascadeOnDelete();
            $table->enum('kind', ['home', 'work', 'reference'])->comment('ประเภทที่อยู่: home=บ้าน, work=ที่ทำงาน, reference=ผู้รับรอง');
            $table->string('dwelling_type', 100)->nullable()->comment('ประเภทที่อยู่อาศัย');
            $table->string('residence_status', 100)->nullable()->comment('สถานะที่อยู่อาศัย (เช่า/เป็นเจ้าของ/อื่นๆ)');
            $table->decimal('residence_rent_amount', 12, 2)->nullable()->comment('ค่าเช่าที่อยู่อาศัย');
            $table->integer('residence_years')->nullable()->comment('ระยะเวลาที่อาศัย (ปี)');
            $table->string('address_no', 50)->nullable()->comment('เลขที่');
            $table->string('address_floor', 30)->nullable()->comment('ชั้น');
            $table->string('address_village', 100)->nullable()->comment('หมู่บ้าน/หมู่ที่');
            $table->string('address_building', 100)->nullable()->comment('อาคาร/ตึก');
            $table->string('address_soi', 100)->nullable()->comment('ซอย');
            $table->string('address_road', 100)->nullable()->comment('ถนน');
            $table->string('address_subdistrict', 100)->nullable()->comment('แขวง/ตำบล');
            $table->string('address_district', 100)->nullable()->comment('เขต/อำเภอ');
            $table->string('address_province', 100)->nullable()->comment('จังหวัด');
            $table->string('address_postal', 20)->nullable()->comment('รหัสไปรษณีย์');
            $table->timestamp('created_at')->nullable()->comment('วันที่เวลาสร้างข้อมูล');
            $table->timestamp('updated_at')->nullable()->comment('วันที่เวลาแก้ไขล่าสุด');

            $table->unique(['application_id', 'kind']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consent_addresses');
    }
};
