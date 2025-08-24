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
        // Participantes
        Schema::create('participants', function (Blueprint $table) {
            $table->id();
            $table->string('document', 20)->unique();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email')->unique();
            $table->enum('role', ['Estudiante', 'Docente']);
            $table->enum('affiliation', ['Catedratico', 'Ocasional','Planta'])->nullable();
            $table->foreignId('program_id')->nullable()->constrained('programs')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};
