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
        Schema::create('exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_topic_id')
                ->constrained('learning_topics')
                ->cascadeOnDelete();
            $table->string('number');
            $table->string('title');
            $table->string('slug');
            $table->text('description');
            $table->longText('starter_code')->nullable();
            $table->json('test_cases')->nullable();
            $table->string('difficulty')->default('basic');
            $table->unsignedInteger('position');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['learning_topic_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercises');
    }
};
