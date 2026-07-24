<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consent_contacts', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key');
            $table->foreignId('applicant_id')->nullable()->comment('อ้างอิงผู้สมัคร (consent_request_applicants)')->constrained('consent_request_applicants')->cascadeOnDelete();
            $table->string('phone_home', 20)->nullable()->comment('เบอร์โทรศัพท์บ้าน');
            $table->string('phone_mobile', 20)->nullable()->comment('เบอร์โทรศัพท์มือถือ');
            $table->string('email', 100)->nullable()->comment('อีเมล');
            $table->timestamp('created_at')->nullable()->comment('วันที่เวลาสร้างข้อมูล');
            $table->timestamp('updated_at')->nullable()->comment('วันที่เวลาแก้ไขล่าสุด');

            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consent_contacts');
    }
};

