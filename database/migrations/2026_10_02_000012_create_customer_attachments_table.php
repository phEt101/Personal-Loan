<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_attachments', function (Blueprint $table) {
            $table->id();
            $table->string('CustomerNo', 16);
            $table->string('DocumentName');
            $table->foreignId('DocumentTypeId')->constrained('document_types');
            $table->string('OriginalName');
            $table->string('FilePath');
            $table->string('MimeType', 100)->nullable();
            $table->unsignedBigInteger('FileSize');
            $table->foreignId('UploadedBy')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('CustomerNo')->references('CustomerNo')->on('customers')->cascadeOnDelete();
            $table->index(['CustomerNo', 'DocumentTypeId']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_attachments');
    }
};
