<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('address_customers', function (Blueprint $table) {
            $table->id();

            // H Meter source: dbo.CustomerAddress, linked by CustomerNo and AddressId.
            $table->string('CustomerNo', 16);
            $table->unsignedInteger('AddressId');
            $table->string('AddressLine1', 100);
            $table->string('AddressLine2', 100);

            // H Meter lookup sources: Province, District, and SubDistrict.
            $table->string('ProvinceCode', 20)->nullable();
            $table->string('ProvinceDesc', 150)->nullable();
            $table->string('DistrictCode', 20)->nullable();
            $table->string('DistrictDesc', 150)->nullable();
            $table->string('SubDistrictCode', 20)->nullable();
            $table->string('SubDistrictDesc', 150)->nullable();
            $table->string('ZipCode', 10);

            // H Meter lookup source: AddressType; InsertUserId comes from dbo.User.
            $table->boolean('Status')->default(true);
            $table->dateTime('InsertDate')->nullable();
            $table->unsignedInteger('InsertUserId')->nullable();
            $table->unsignedInteger('AddressTypeCode')->nullable();
            $table->timestamps();

            $table->unique(['CustomerNo', 'AddressId']);
            $table->index('CustomerNo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('address_customers');
    }
};