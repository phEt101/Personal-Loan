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
                if (!Schema::hasColumn('consent_requests', 'product_type')) {
                    $table->string('product_type', 50)->nullable()->after('officer_group')->comment('ประเภทผลิตภัณฑ์');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('consent_requests')) {
            Schema::table('consent_requests', function (Blueprint $table) {
                if (Schema::hasColumn('consent_requests', 'product_type')) {
                    $table->dropColumn('product_type');
                }
            });
        }
    }
};
