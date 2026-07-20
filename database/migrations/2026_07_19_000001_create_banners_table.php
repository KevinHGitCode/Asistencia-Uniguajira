<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // nombre interno / patrocinador
            $table->string('target_url')->nullable(); // enlace del anuncio (opcional)
            $table->date('starts_at')->nullable(); // vigencia: desde (null = sin límite)
            $table->date('ends_at')->nullable();   // vigencia: hasta (null = sin límite)
            $table->boolean('active')->default(true);
            // Métricas para poder rendir cuentas al patrocinador (impresiones
            // servidas en la página pública y clics sobre el enlace).
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->timestamps();
        });

        Schema::create('banner_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('banner_id')->unique()->constrained('banners')->cascadeOnDelete();
            // La imagen del banner guardada como base64 en un longText, igual
            // que los PDF de formato (ADR-0017): portable entre SQLite y MySQL
            // y durable en Hostinger, donde el disco público no es confiable.
            $table->longText('content');
            $table->string('mime')->nullable();
            $table->unsignedInteger('size')->nullable();
            $table->string('hash', 64)->nullable(); // sha256 de los bytes crudos
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banner_files');
        Schema::dropIfExists('banners');
    }
};
