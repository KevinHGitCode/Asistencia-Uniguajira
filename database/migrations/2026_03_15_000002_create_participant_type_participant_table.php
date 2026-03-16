<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participant_type_participant', function (Blueprint $table) {
            $table->foreignId('participant_id')
                  ->constrained('participants')
                  ->cascadeOnDelete();
            $table->foreignId('participant_type_id')
                  ->constrained('participant_types')
                  ->cascadeOnDelete();
            $table->primary(['participant_id', 'participant_type_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participant_type_participant');
    }
};
