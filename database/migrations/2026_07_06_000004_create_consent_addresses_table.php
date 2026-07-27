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
            $table->foreignId('applicant_id')->nullable()->comment('อ้างอิงผู้สมัคร (consent_request_applicants)')->constrained('consent_request_applicants')->cascadeOnDelete();
            $table->enum('kind', ['home', 'work', 'reference/guarantor', 'reference/guarantor_work', 'document'])->comment('ประเภทที่อยู่: home=บ้าน, work=ที่ทำงาน, reference=ผู้รับรอง, reference/guarantor_work=ที่ทำงานของผู้ค้ำ, document=ที่อยู่ตามเอกสารสำคัญ');
            $table->string('residence_status', 100)->nullable()->comment('สถานะที่อยู่อาศัย (เช่า/เป็นเจ้าของ/อื่นๆ)');
            $table->text('address_text')->nullable()->comment('ที่อยู่แบบข้อความอิสระ');
            $table->string('address_room', 50)->nullable()->comment('เลขที่ห้อง');
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
            $table->text('birth_place_address')->nullable()->comment('ที่อยู่บ้านเกิด (สำหรับชาวต่างชาติ)');
            $table->timestamp('created_at')->nullable()->comment('วันที่เวลาสร้างข้อมูล');
            $table->timestamp('updated_at')->nullable()->comment('วันที่เวลาแก้ไขล่าสุด');

            $table->unique(['applicant_id', 'kind']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consent_addresses');
    }
};
