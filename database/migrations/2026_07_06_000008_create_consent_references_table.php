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
            $table->foreignId('application_id')->comment('อ้างอิงใบคำขอ (consent_requests)')->constrained('consent_requests')->cascadeOnDelete()->unique();
            $table->string('ref_name', 150)->nullable()->comment('ชื่อ-นามสกุลผู้รับรอง');
            $table->string('ref_relation', 100)->nullable()->comment('ความสัมพันธ์กับผู้กู้');
            $table->string('ref_phone_home', 20)->nullable()->comment('เบอร์โทรบ้านผู้รับรอง');
            $table->string('ref_phone_mobile', 20)->nullable()->comment('เบอร์มือถือผู้รับรอง');
            $table->timestamp('created_at')->nullable()->comment('วันที่เวลาสร้างข้อมูล');
            $table->timestamp('updated_at')->nullable()->comment('วันที่เวลาแก้ไขล่าสุด');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consent_references');
    }
};

