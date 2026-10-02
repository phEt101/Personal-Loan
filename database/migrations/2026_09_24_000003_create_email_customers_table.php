<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_emails', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();

            // อ้างอิงข้อมูลลูกค้าจากตาราง customers
            $table->string('CustomerNo', 16);
            $table->unsignedInteger('EmailId');
            $table->string('Email', 100);

            // Legacy audit fields retained for customer email records.
            $table->dateTime('CreateDateTime')->nullable();
            $table->unsignedInteger('CreateUserId')->nullable();
            $table->unsignedInteger('UpdateUserId')->nullable();
            $table->dateTime('UpdateDateTime')->nullable();

            $table->boolean('Status')->default(true);
            $table->string('Remark', 100)->nullable();

            $table->unique(['CustomerNo', 'EmailId']);
            $table->index('CustomerNo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_emails');
    }
};
