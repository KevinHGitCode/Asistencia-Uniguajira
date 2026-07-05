<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('format_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('format_id')->unique()->constrained('formats')->cascadeOnDelete();
            // El binario del PDF guardado como base64 en un longText: portable
            // entre SQLite (local) y MySQL (Hostinger) y sin el límite de 64 KB
            // de un BLOB de MySQL. Los PDF de formato pesan ≤ 5 MB (ADR-0017).
            $table->longText('content');
            $table->string('mime')->nullable();
            $table->unsignedInteger('size')->nullable();
            $table->string('hash', 64)->nullable(); // sha256 de los bytes crudos
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('format_files');
    }
};
