<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained()->cascadeOnDelete();
            $table->foreignId('participant_role_id')->nullable()->constrained('participant_roles')->nullOnDelete();
            $table->string('gender', 30)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('neighborhood', 100)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('priority_group', 150)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_details');
    }
};
