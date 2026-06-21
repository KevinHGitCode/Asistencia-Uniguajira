<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('original_filename');
            // en_revision | aprobado | rechazado
            $table->string('status', 20)->default('en_revision');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('new_count')->default(0);
            $table->unsignedInteger('update_count')->default(0);
            $table->unsignedInteger('skipped_count')->default(0);
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_batches');
    }
};
