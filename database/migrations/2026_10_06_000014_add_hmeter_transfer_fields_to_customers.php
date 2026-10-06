<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('HmeterTransferStatus', 20)->default('pending')->index();
            $table->dateTime('HmeterTransferredAt')->nullable();
            $table->foreignId('HmeterTransferredBy')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('AttachmentPurgeAfter')->nullable()->index();
            $table->dateTime('AttachmentsPurgedAt')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('HmeterTransferredBy');
            $table->dropIndex(['HmeterTransferStatus']);
            $table->dropIndex(['AttachmentPurgeAfter']);
            $table->dropColumn([
                'HmeterTransferStatus',
                'HmeterTransferredAt',
                'AttachmentPurgeAfter',
                'AttachmentsPurgedAt',
            ]);
        });
    }
};
