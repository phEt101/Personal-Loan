<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consent_requests', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key');
            $table->string('encrypted_id', 255)->nullable()->comment('รหัสอ้างอิงที่ถูกเข้ารหัส');
            $table->date('app_date')->nullable()->comment('วันที่ยื่นคำขอ');
            $table->string('app_no', 50)->nullable()->comment('เลขที่ใบสมัคร');
            $table->string('officer_name', 100)->nullable()->comment('ชื่อเจ้าหน้าที่ผู้รับเรื่อง');
            $table->string('officer_phone', 20)->nullable()->comment('เบอร์โทรเจ้าหน้าที่ผู้รับเรื่อง');
            $table->string('document_delivery', 50)->nullable()->comment('ช่องทางการรับเอกสาร');
            $table->enum('status', ['กำลังดำเนินการ', 'ผ่าน', 'ไม่ผ่าน'])->default('กำลังดำเนินการ')->comment('สถานะคำขอ');
            $table->enum('loan_status', ['รอวิเคราะห์ 1/2', 'รอวิเคราะห์ 1/2 (รอเอกสารเพิ่มเติม)', 'รอวิเคราะห์ 2/2', 'รอวิเคราะห์ 2/2 (รอเอกสารเพิ่มเติม)', 'รอพิจารณา','อนุมัติ','ไม่อนุมัติ'])->default('รอวิเคราะห์ 1/2')->comment('สถานะสินเชื่อ (สำหรับการอนุมัติสินเชื่อ)');
            $table->foreignId('loan_product_id')->nullable()->constrained('loan_products')->nullOnDelete();
            $table->foreignId('officer_group_id')->nullable()->constrained('officer_groups')->nullOnDelete();
            $table->boolean('signed')->default(false)->comment('ระบุว่าเซ็นเอกสารแล้วหรือไม่');
            $table->dateTime('signed_at')->nullable()->comment('วันที่เวลาเซ็นเอกสาร');
            $table->text('signature_data')->nullable()->comment('ข้อมูลลายเซ็น (เช่น base64)');
            $table->timestamp('created_at')->nullable()->comment('วันที่เวลาสร้างข้อมูล');
            $table->timestamp('updated_at')->nullable()->comment('วันที่เวลาแก้ไขล่าสุด');
            $table->softDeletes()->comment('วันที่เวลาลบ (soft delete)');

            $table->index('status');
            $table->index('signed_at');
            $table->unique('encrypted_id');
            $table->unique('app_no');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consent_requests');
    }
};
