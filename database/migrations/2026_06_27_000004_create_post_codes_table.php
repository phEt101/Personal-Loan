<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_codes', function (Blueprint $table) {
            $table->id();
            $table->string('post_code', 10);
            $table->string('district', 100);
            $table->string('city', 100);
            $table->string('province', 100);
            $table->string('country_code', 10)->default('TH');
            $table->timestamps();

            $table->index('post_code');
            $table->index(['province', 'city']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_codes');
    }
};

