<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_delegations', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->foreignId('responsibility_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('delegator_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('delegate_user_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->text('note')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->dateTime('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['delegate_user_id', 'starts_at', 'ends_at'], 'work_delegations_active_lookup');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->foreign('HmeterWorkDelegationId')
                ->references('id')
                ->on('work_delegations')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['HmeterWorkDelegationId']);
        });

        Schema::dropIfExists('work_delegations');
    }
};
