<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Allow the same dependency name in different campuses.
     */
    public function up(): void
    {
        Schema::table('dependencies', function (Blueprint $table) {
            $table->dropUnique('dependencies_name_unique');
            $table->unique(['campus_id', 'name'], 'dependencies_campus_id_name_unique');
        });
    }

    /**
     * Restore the legacy global uniqueness constraint.
     */
    public function down(): void
    {
        Schema::table('dependencies', function (Blueprint $table) {
            $table->dropUnique('dependencies_campus_id_name_unique');
            $table->unique('name');
        });
    }
};
