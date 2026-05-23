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
        Schema::create('exercise_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('exercise_id')
                ->constrained('exercises')
                ->cascadeOnDelete();
            $table->longText('source_code');
            $table->unsignedInteger('language_id')->nullable();
            $table->string('status')->default('pending');
            $table->unsignedInteger('judge0_status_id')->nullable();
            $table->text('stdout')->nullable();
            $table->text('stderr')->nullable();
            $table->text('compile_output')->nullable();
            $table->decimal('execution_time', 8, 3)->nullable();
            $table->unsignedInteger('memory')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercise_attempts');
    }
};
