<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phone_customers', function (Blueprint $table) {
            $table->id();

            // H Meter source: dbo.CustomerPhone, linked by CustomerNo and PhoneId.
            $table->string('CustomerNo', 16);
            $table->unsignedInteger('PhoneId');
            $table->unsignedInteger('PhoneSequense')->default(0);
            $table->string('Phone', 15);

            // H Meter lookup source: PhoneType.
            $table->string('PhoneType', 10);
            $table->boolean('Status')->default(true);
            $table->timestamps();

            $table->unique(['CustomerNo', 'PhoneId']);
            $table->index('CustomerNo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phone_customers');
    }
};