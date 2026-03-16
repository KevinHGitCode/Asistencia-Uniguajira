<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained()->cascadeOnDelete();
            $table->string('sexo', 50)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('municipio', 100);
            $table->string('barrio', 100)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->string('grupo_priorizado', 150)->nullable();
            // Programa con el que el participante asistió (si tiene varios)
            $table->foreignId('program_id')->nullable()->constrained('programs')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_details');
    }
};
