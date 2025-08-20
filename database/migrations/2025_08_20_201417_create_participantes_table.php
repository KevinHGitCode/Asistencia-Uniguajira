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
        Schema::create('participantes', function (Blueprint $table) {
            $table->id();
            $table->string('documento', 20)->unique();
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->string('email')->unique();
            $table->foreignId('estamento_id')->constrained('estamentos')->onDelete('cascade');
            $table->foreignId('programa_id')->nullable()->constrained('programas')->onDelete('cascade');
            $table->foreignId('vinculacion_id')->nullable()->constrained('vinculaciones')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participantes');
    }
};
