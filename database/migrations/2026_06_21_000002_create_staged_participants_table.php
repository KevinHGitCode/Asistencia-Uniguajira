<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staged_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_batch_id')->constrained('import_batches')->cascadeOnDelete();
            // nuevo | actualiza | omitido
            $table->string('status', 20);
            $table->string('document', 50)->nullable();
            $table->string('first_name', 150)->nullable();
            $table->string('last_name', 150)->nullable();
            $table->string('email')->nullable();
            // Referencia suave al participante existente (solo en filas 'actualiza').
            $table->unsignedBigInteger('existing_participant_id')->nullable();
            // Roles resueltos: [{participant_type_id, program_id, dependency_id, affiliation_id}, ...]
            $table->json('roles')->nullable();
            // Motivo de la omisión (solo en filas 'omitido').
            $table->string('error')->nullable();
            // Fila original del Excel (para descargar omitidos / referencia).
            $table->json('raw')->nullable();
            $table->timestamps();

            $table->index(['import_batch_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staged_participants');
    }
};
