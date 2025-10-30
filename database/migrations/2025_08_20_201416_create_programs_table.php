<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Programas
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->enum('program_type', ['Pregrado', 'Posgrado'])->nullable();
            $table->string('campus', 100)->nullable();
            $table->timestamps();

            // Clave única para la combinación de nombre y campus
            $table->unique(['name', 'campus']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
