<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['participant_id']);
            $table->foreign('participant_id')
                ->references('id')
                ->on('participants')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['participant_id']);
            $table->foreign('participant_id')
                ->references('id')
                ->on('participants');
        });
    }
};
