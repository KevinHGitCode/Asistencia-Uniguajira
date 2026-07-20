<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            // Peso de la rotación ponderada (ADR-0030, fase 4): un banner con
            // weight 3 aparece 3 veces más que uno con weight 1.
            $table->unsignedSmallInteger('weight')->default(1)->after('active');
        });
    }

    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn('weight');
        });
    }
};
