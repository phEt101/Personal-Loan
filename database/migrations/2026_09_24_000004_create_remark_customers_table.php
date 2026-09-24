<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('remark_customers', function (Blueprint $table) {
            $table->id();

            // H Meter source: dbo.CustomerRemark, linked by CustomerNo and RemarkId.
            $table->string('CustomerNo', 16);
            $table->unsignedInteger('RemarkId');
            $table->text('Comment');

            // H Meter audit fields; InsertUserId and UpdateUserId come from dbo.User.
            $table->dateTime('InsertDateTime')->nullable();
            $table->unsignedInteger('InsertUserId')->nullable();
            $table->unsignedInteger('UpdateUserId')->nullable();
            $table->dateTime('UpdateDateTime')->nullable();
            $table->timestamps();

            $table->unique(['CustomerNo', 'RemarkId']);
            $table->index('CustomerNo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('remark_customers');
    }
};