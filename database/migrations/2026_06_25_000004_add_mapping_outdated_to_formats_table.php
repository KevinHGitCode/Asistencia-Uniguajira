<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('formats', function (Blueprint $table) {
            // Marca que el PDF cambió después del último mapeo, así que las
            // coordenadas guardadas ya no corresponden al archivo (ADR-0015).
            $table->boolean('mapping_outdated')->default(false)->after('mapping');
        });
    }

    public function down(): void
    {
        Schema::table('formats', function (Blueprint $table) {
            $table->dropColumn('mapping_outdated');
        });
    }
};
