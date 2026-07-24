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
            $table->foreignId('applicant_id')->comment('อ้างอิงผู้สมัคร (consent_request_applicants)')->constrained('consent_request_applicants')->cascadeOnDelete()->unique();
            $table->string('bank_name', 100)->nullable()->comment('ชื่อธนาคาร');
            $table->string('account_name', 150)->nullable()->comment('ชื่อบัญชี');
            $table->string('account_type', 50)->nullable()->comment('ประเภทบัญชี');
            $table->string('account_number', 50)->nullable()->comment('เลขที่บัญชี');
            $table->string('payment_method', 100)->nullable()->comment('วิธีการชำระเงิน');
            $table->decimal('direct_debit_amount', 12, 2)->nullable()->comment('จำนวนเงินหักบัญชีต่อเดือน');
            $table->string('direct_debit_account_number', 50)->nullable()->comment('เลขที่บัญชีสำหรับหักเงิน');
            $table->timestamp('created_at')->nullable()->comment('วันที่เวลาสร้างข้อมูล');
            $table->timestamp('updated_at')->nullable()->comment('วันที่เวลาแก้ไขล่าสุด');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consent_disbursement_accounts');
    }
};
