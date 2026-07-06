<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consent_disbursement_accounts', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key');
            $table->foreignId('application_id')->comment('อ้างอิงใบคำขอ (consent_requests)')->constrained('consent_requests')->cascadeOnDelete()->unique();
            $table->string('bank_name', 100)->nullable()->comment('ชื่อธนาคาร');
            $table->string('bank_branch', 100)->nullable()->comment('สาขาธนาคาร');
            $table->string('account_name', 150)->nullable()->comment('ชื่อบัญชี');
            $table->string('account_type', 50)->nullable()->comment('ประเภทบัญชี');
            $table->string('account_number', 50)->nullable()->comment('เลขที่บัญชี');
            $table->timestamp('created_at')->nullable()->comment('วันที่เวลาสร้างข้อมูล');
            $table->timestamp('updated_at')->nullable()->comment('วันที่เวลาแก้ไขล่าสุด');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consent_disbursement_accounts');
    }
};
