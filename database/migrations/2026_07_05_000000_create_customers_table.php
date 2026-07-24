<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code', 50)->unique()->comment('รหัสลูกค้า');
            $table->string('title', 50)->nullable()->comment('คำนำหน้า');
            $table->string('name', 150)->nullable()->comment('ชื่อ-นามสกุล');
            $table->string('name_en', 150)->nullable()->comment('ชื่อ-นามสกุล (อังกฤษ)');
            $table->date('birthdate')->nullable()->comment('วันเดือนปีเกิด');
            $table->string('id_card', 20)->nullable()->comment('เลขบัตรประชาชน');
            $table->string('passport', 20)->nullable()->comment('เลขหนังสือเดินทาง');
            $table->string('nationality', 50)->nullable()->comment('สัญชาติ');
            $table->string('phone_number', 20)->nullable()->comment('หมายเลขโทรศัพท์');
            $table->string('email', 100)->nullable()->comment('อีเมล');
            $table->string('address', 255)->nullable()->comment('ที่อยู่');
            $table->string('occupation', 100)->nullable()->comment('อาชีพ');
            $table->softDeletes();
            $table->timestamps();

            $table->index('id_card');
            $table->index('passport');
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
