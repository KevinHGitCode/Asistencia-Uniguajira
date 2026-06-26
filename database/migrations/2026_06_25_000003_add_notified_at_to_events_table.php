<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Marcas de idempotencia para los avisos in-app (ADR-0018): evitan
            // reenviar el mismo recordatorio en cada tick del cron.
            $table->timestamp('reminder_notified_at')->nullable()->after('ended_at');
            $table->timestamp('ending_notified_at')->nullable()->after('reminder_notified_at');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['reminder_notified_at', 'ending_notified_at']);
        });
    }
};
