<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_remarks', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();

            // อ้างอิงข้อมูลลูกค้าจากตาราง customers
            $table->string('CustomerNo', 16);
            $table->unsignedInteger('RemarkId');
            $table->text('Comment');

            // Legacy audit fields retained for customer remark records.
            $table->dateTime('InsertDateTime')->nullable();
            $table->unsignedInteger('InsertUserId')->nullable();
            $table->unsignedInteger('UpdateUserId')->nullable();
            $table->dateTime('UpdateDateTime')->nullable();


            $table->unique(['CustomerNo', 'RemarkId']);
            $table->index('CustomerNo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_remarks');
    }
};
