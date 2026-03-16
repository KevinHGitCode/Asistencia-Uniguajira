<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participant_type_participant', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('participant_type_id');
        });

        Schema::table('participant_program', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('program_id');
        });

        Schema::table('affiliation_participant', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('affiliation_id');
        });
    }

    public function down(): void
    {
        Schema::table('participant_type_participant', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });

        Schema::table('participant_program', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });

        Schema::table('affiliation_participant', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};