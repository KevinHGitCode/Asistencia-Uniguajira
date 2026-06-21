<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('import_batches', function (Blueprint $table) {
            // Tiempo de procesamiento del cargue (parse + staging), en milisegundos.
            // Solo para mediciones internas; no se muestra en la UI.
            $table->unsignedInteger('duration_ms')->nullable()->after('skipped_count');
        });
    }

    public function down(): void
    {
        Schema::table('import_batches', function (Blueprint $table) {
            $table->dropColumn('duration_ms');
        });
    }
};
