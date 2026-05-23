<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_exercise_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('exercise_id')
                ->constrained('exercises')
                ->cascadeOnDelete();
            $table->string('status')->default('not_started');
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('last_attempt_id')
                ->nullable()
                ->constrained('exercise_attempts')
                ->nullOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'exercise_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_exercise_progress');
    }
};
