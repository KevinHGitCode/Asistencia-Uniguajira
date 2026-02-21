<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('location')->nullable();
            $table->text('link');

            // Usuario dueño del evento
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Dependencia del evento (siempre debe existir)
            $table->foreignId('dependency_id')
                ->nullable()
                ->constrained('dependencies')
                ->onUpdate('cascade')
                ->onDelete('set null');

            // Área opcional (solo cuando la dependencia tiene áreas)
            $table->foreignId('area_id')
                ->nullable()
                ->constrained('areas')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
