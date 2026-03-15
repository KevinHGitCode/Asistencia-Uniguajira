<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participant_program', function (Blueprint $table) {
            $table->foreignId('participant_id')->constrained('participants')->cascadeOnDelete();
            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $table->primary(['participant_id', 'program_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participant_program');
    }
};
