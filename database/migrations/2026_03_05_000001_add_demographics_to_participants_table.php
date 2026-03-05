<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->string('sexo', 30)->nullable()->after('affiliation');
            $table->string('grupo_priorizado', 30)->nullable()->after('sexo');
        });

        // Poblar con datos representativos para demo/desarrollo
        // Se usa id % N para distribuir de forma determinista y rápida
        DB::statement("
            UPDATE participants SET
                sexo = CASE
                    WHEN (id % 10) IN (0,1,2,3,4) THEN 'Femenino'
                    WHEN (id % 10) IN (5,6,7,8)   THEN 'Masculino'
                    ELSE 'Otro'
                END,
                grupo_priorizado = CASE
                    WHEN (id % 20) IN (0,1,2)  THEN 'Indígena'
                    WHEN (id % 20) IN (3,4)    THEN 'Afrodescendiente'
                    WHEN (id % 20) = 5         THEN 'Raizal'
                    WHEN (id % 20) = 6         THEN 'Palenquero'
                    WHEN (id % 20) = 7         THEN 'Rom'
                    ELSE 'Ninguno'
                END
        ");
    }

    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropColumn(['sexo', 'grupo_priorizado']);
        });
    }
};
