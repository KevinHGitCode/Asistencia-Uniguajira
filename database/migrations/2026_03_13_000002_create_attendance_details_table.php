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
            $table->string('phone', 20)->nullable();
            $table->string('address', 100)->nullable();
            $table->string('neighborhood', 30)->nullable();
            $table->string('city', 30)->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('priority_group', 70)->nullable();
            // Programa con el que el participante asistió (si tiene varios)
            $table->foreignId('program_id')->nullable()->constrained('programs')->nullOnDelete();
            // Tipo de estamento con el que el participante se registró (si tiene varios)
            $table->foreignId('participant_type_id')->nullable()->constrained('participant_types')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_details');
    }
};
