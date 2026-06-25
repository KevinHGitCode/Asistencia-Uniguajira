<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->foreignId('academic_program_id')
                ->nullable()
                ->after('campus_id')
                ->index('programs_academic_program_id_index')
                ->constrained('academic_programs')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropForeign(['academic_program_id']);
            $table->dropIndex('programs_academic_program_id_index');
            $table->dropColumn('academic_program_id');
        });
    }
};
