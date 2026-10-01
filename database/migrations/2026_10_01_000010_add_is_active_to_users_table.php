<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!$this->columnExists('is_active')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('note');
            });
        }
    }

    public function down(): void
    {
        if ($this->columnExists('is_active')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }
    }

    private function columnExists(string $column): bool
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return Schema::hasColumn('users', $column);
        }

        return DB::table('information_schema.columns')
            ->whereRaw('table_schema = database()')
            ->where('table_name', 'users')
            ->where('column_name', $column)
            ->exists();
    }
};
