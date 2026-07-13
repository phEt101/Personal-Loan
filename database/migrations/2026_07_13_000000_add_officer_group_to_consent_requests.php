<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('consent_requests')) {
            Schema::table('consent_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('consent_requests', 'officer_group')) {
                    $table->string('officer_group', 50)->nullable()->after('officer_phone')->comment('Officer group identifier');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('consent_requests')) {
            Schema::table('consent_requests', function (Blueprint $table) {
                if (Schema::hasColumn('consent_requests', 'officer_group')) {
                    $table->dropColumn('officer_group');
                }
            });
        }
    }
};
