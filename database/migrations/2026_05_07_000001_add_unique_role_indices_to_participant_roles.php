<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega índices únicos compuestos a participant_roles como red de seguridad
     * para evitar roles duplicados por participante + estamento + entidad.
     *
     * MySQL/SQLite tratan NULLs como valores distintos en índices únicos,
     * por lo que estos índices solo se aplican cuando la columna de entidad
     * tiene un valor concreto (no NULL).
     */
    public function up(): void
    {
        // Limpiar duplicados existentes antes de crear los índices
        $this->cleanDuplicates('program_id');
        $this->cleanDuplicates('dependency_id');
        $this->cleanDuplicates('organization_id');

        Schema::table('participant_roles', function (Blueprint $table) {
            $table->unique(
                ['participant_id', 'participant_type_id', 'program_id'],
                'pr_unique_program'
            );
            $table->unique(
                ['participant_id', 'participant_type_id', 'dependency_id'],
                'pr_unique_dependency'
            );
            $table->unique(
                ['participant_id', 'participant_type_id', 'organization_id'],
                'pr_unique_organization'
            );
        });
    }

    public function down(): void
    {
        Schema::table('participant_roles', function (Blueprint $table) {
            $table->dropUnique('pr_unique_program');
            $table->dropUnique('pr_unique_dependency');
            $table->dropUnique('pr_unique_organization');
        });
    }

    /**
     * Elimina filas duplicadas para una columna de entidad dada,
     * conservando el registro activo más reciente de cada grupo.
     */
    private function cleanDuplicates(string $entityColumn): void
    {
        $duplicates = DB::table('participant_roles')
            ->select('participant_id', 'participant_type_id', $entityColumn)
            ->whereNotNull($entityColumn)
            ->groupBy('participant_id', 'participant_type_id', $entityColumn)
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $dup) {
            // Conservar el registro activo más reciente
            $keep = DB::table('participant_roles')
                ->where('participant_id', $dup->participant_id)
                ->where('participant_type_id', $dup->participant_type_id)
                ->where($entityColumn, $dup->$entityColumn)
                ->orderByDesc('is_active')
                ->orderByDesc('updated_at')
                ->first();

            if ($keep) {
                DB::table('participant_roles')
                    ->where('participant_id', $dup->participant_id)
                    ->where('participant_type_id', $dup->participant_type_id)
                    ->where($entityColumn, $dup->$entityColumn)
                    ->where('id', '!=', $keep->id)
                    ->delete();
            }
        }
    }
};
