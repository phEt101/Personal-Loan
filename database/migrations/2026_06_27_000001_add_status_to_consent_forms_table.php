<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('consent_forms', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->after('signature_data');
        });
    }

    public function down()
    {
        Schema::table('consent_forms', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
