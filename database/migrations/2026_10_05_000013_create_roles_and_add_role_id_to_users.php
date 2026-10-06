<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();
        DB::table('roles')->insert([
            [
                'name' => 'Administrator',
                'slug' => 'admin',
                'description' => 'Full access to system administration.',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Manager',
                'slug' => 'manager',
                'description' => 'Can view customer records created by external users.',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Standard user',
                'slug' => 'user',
                'description' => 'Standard access to the application.',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('user_type')->constrained('roles');
        });

        DB::table('users')->update([
            'role_id' => DB::raw("CASE role WHEN 'admin' THEN (SELECT id FROM roles WHERE slug = 'admin') ELSE (SELECT id FROM roles WHERE slug = 'user') END"),
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->nullable(false)->change();
            $table->dropColumn('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'user'])->default('user')->after('user_type');
        });

        DB::table('users')->update([
            'role' => DB::raw("CASE role_id WHEN (SELECT id FROM roles WHERE slug = 'admin') THEN 'admin' ELSE 'user' END"),
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
        });

        Schema::dropIfExists('roles');
    }
};
