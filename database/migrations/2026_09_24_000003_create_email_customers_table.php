<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_customers', function (Blueprint $table) {
            $table->id();

            // H Meter source: dbo.CustomerEmail, linked by CustomerNo and EmailId.
            $table->string('CustomerNo', 16);
            $table->unsignedInteger('EmailId');
            $table->string('Email', 100);
            $table->boolean('Status')->default(true);

            // H Meter audit fields; CreateUserId and UpdateUserId come from dbo.User.
            $table->dateTime('CreateDateTime')->nullable();
            $table->unsignedInteger('CreateUserId')->nullable();
            $table->unsignedInteger('UpdateUserId')->nullable();
            $table->dateTime('UpdateDateTime')->nullable();
            $table->timestamps();

            $table->unique(['CustomerNo', 'EmailId']);
            $table->index('CustomerNo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_customers');
    }
};