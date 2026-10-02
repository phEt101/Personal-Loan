<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_phones', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            // อ้างอิงข้อมูลลูกค้าจากตาราง customers
            $table->string('CustomerNo', 16);
            $table->unsignedInteger('PhoneId');
            $table->string('Remark', 100)->nullable();
            $table->string('Phone', 15);
            // อ้างอิงประเภทโทรศัพท์จากตาราง phone_types
            $table->string('PhoneType', 2);
            // กำหนดค่า PhoneSequense เป็น 0 เสมอ
            $table->unsignedInteger('PhoneSequense')->default(0);
            $table->boolean('Status')->default(true);

            $table->unique(['CustomerNo', 'PhoneId']);
            $table->index('CustomerNo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_phones');
    }
};
