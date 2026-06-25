<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('campus_id')
                ->nullable()
                ->index('users_campus_id_index')
                ->constrained('campuses')
                ->nullOnDelete();
        });

        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('campus_id')
                ->nullable()
                ->index('events_campus_id_index')
                ->constrained('campuses')
                ->nullOnDelete();
        });

        Schema::table('dependencies', function (Blueprint $table) {
            $table->foreignId('campus_id')
                ->nullable()
                ->index('dependencies_campus_id_index')
                ->constrained('campuses')
                ->nullOnDelete();
        });

        Schema::table('programs', function (Blueprint $table) {
            $table->foreignId('campus_id')
                ->nullable()
                ->index('programs_campus_id_index')
                ->constrained('campuses')
                ->nullOnDelete();
        });

        Schema::table('areas', function (Blueprint $table) {
            $table->foreignId('campus_id')
                ->nullable()
                ->index('areas_campus_id_index')
                ->constrained('campuses')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        foreach (['users', 'events', 'dependencies', 'programs', 'areas'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->dropForeign(['campus_id']);
                $table->dropIndex("{$tableName}_campus_id_index");
                $table->dropColumn('campus_id');
            });
        }
    }
};
