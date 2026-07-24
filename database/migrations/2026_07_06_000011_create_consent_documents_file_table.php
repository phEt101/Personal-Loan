<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consent_documents_file', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key');
            $table->foreignId('applicant_id')->comment('อ้างอิงผู้สมัคร (consent_request_applicants)')->constrained('consent_request_applicants')->cascadeOnDelete();
            $table->string('document_type', 50)->nullable()->comment('ประเภทเอกสาร');
            $table->string('disk', 50)->default('local')->comment('Storage disk');
            $table->string('path', 1024)->comment('Storage path');
            $table->string('original_name', 255)->comment('ชื่อไฟล์เดิม');
            $table->string('mime_type', 100)->nullable()->comment('ประเภทไฟล์');
            $table->unsignedBigInteger('size')->nullable()->comment('ขนาดไฟล์ (bytes)');
            $table->timestamp('created_at')->nullable()->comment('วันที่เวลาสร้างข้อมูล');
            $table->timestamp('updated_at')->nullable()->comment('วันที่เวลาแก้ไขล่าสุด');

            $table->index('applicant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consent_documents_file');
    }
};
