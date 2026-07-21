<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consent_loan_requests', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key');
            $table->foreignId('application_id')->comment('อ้างอิงใบคำขอ (consent_requests)')->constrained('consent_requests')->cascadeOnDelete()->unique();
            $table->integer('loan_term')->nullable()->comment('ระยะเวลากู้ (เดือน)');
            $table->string('loan_amount_type', 20)->nullable()->comment('ประเภทวงเงินกู้');
            $table->decimal('custom_loan_amount', 12, 2)->nullable()->comment('วงเงินกู้ที่ระบุเอง');
            $table->decimal('calculated_eligible_amount', 12, 2)->nullable()->comment('ยอดวงเงินสูงสุดที่คำนวณได้เบื้องต้น');
            $table->string('loan_purpose', 150)->nullable()->comment('วัตถุประสงค์ในการกู้');
            $table->timestamp('created_at')->nullable()->comment('วันที่เวลาสร้างข้อมูล');
            $table->timestamp('updated_at')->nullable()->comment('วันที่เวลาแก้ไขล่าสุด');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consent_loan_requests');
    }
};
