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
        Schema::create('affiliation_participant', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')
                  ->constrained('participants')
                  ->cascadeOnDelete();
            $table->foreignId('affiliation_id')
                  ->constrained('affiliations')
                  ->cascadeOnDelete();
            $table->unique(['participant_id', 'affiliation_id'], 'ap_participant_affiliation_unique');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliation_participant');
    }
};
