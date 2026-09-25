<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            // อ้างอิงข้อมูลลูกค้าจากตาราง customers
            $table->string('CustomerNo', 16);
            $table->unsignedInteger('AddressId');
            $table->string('AddressLine1', 100);
            $table->string('AddressLine2', 100);
            // ดึงข้อมูลจาก H Meter ตาราง dbo.Province
            $table->string('ProvinceCode', 20)->nullable();
            $table->string('ProvinceDesc', 150)->nullable();
            // ดึงข้อมูลจาก H Meter ตาราง dbo.District
            $table->string('DistrictCode', 20)->nullable();
            $table->string('DistrictDesc', 150)->nullable();
            // ดึงข้อมูลจาก H Meter ตาราง dbo.SubDistrict
            $table->string('SubDistrictCode', 20)->nullable();
            $table->string('SubDistrictDesc', 150)->nullable();
            $table->string('ZipCode', 10);
            // กำหนดค่า AddressTypeCode เป็น 1 เสมอ
            $table->unsignedInteger('AddressTypeCode')->default(1);
            $table->string('Remark', 200)->nullable();

            $table->boolean('Status')->default(true);

            $table->unique(['CustomerNo', 'AddressId']);
            $table->index('CustomerNo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
    }
};