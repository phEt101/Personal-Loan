<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('officer_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name_th', 100);
            $table->string('name_en', 100);
            $table->string('institution_code', 20)->nullable()->comment('รหัสประเภทสถาบัน');
            $table->string('loan_type_code', 20)->nullable()->comment('รหัสประเภทสินเชื่อ');
            $table->string('p_loan_regulated_code', 20)->nullable()->comment('รหัสลักษณะสินเชื่อส่วนบุคคลภายใต้การกำกับ');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('officer_groups');
    }
};
