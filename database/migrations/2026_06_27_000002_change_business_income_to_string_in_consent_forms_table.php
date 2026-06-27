<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('consent_forms', function (Blueprint $table) {
            $table->string('business_income', 255)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('consent_forms', function (Blueprint $table) {
            $table->decimal('business_income', 12, 2)->nullable()->change();
        });
    }
};
