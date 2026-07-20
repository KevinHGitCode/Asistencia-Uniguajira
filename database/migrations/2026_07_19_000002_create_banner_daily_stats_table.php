<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Acumulado por día para poder reportar rangos de fechas al
        // patrocinador (ADR-0031). Los totales de banners.impressions/clicks
        // se mantienen como cifra de vida completa.
        Schema::create('banner_daily_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('banner_id')->constrained('banners')->cascadeOnDelete();
            $table->date('date');
            $table->unsignedInteger('impressions')->default(0);
            $table->unsignedInteger('clicks')->default(0);
            $table->timestamps();

            $table->unique(['banner_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banner_daily_stats');
    }
};
