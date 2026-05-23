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
        Schema::table('exercises', function (Blueprint $table) {
            $table->string('exercise_type')->default('function')->after('description');
            $table->string('function_name')->nullable()->after('exercise_type');
            $table->text('input_description')->nullable()->after('function_name');
            $table->text('output_description')->nullable()->after('input_description');
            $table->json('examples')->nullable()->after('output_description');
            $table->json('considerations')->nullable()->after('examples');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exercises', function (Blueprint $table) {
            $table->dropColumn([
                'exercise_type',
                'function_name',
                'input_description',
                'output_description',
                'examples',
                'considerations',
            ]);
        });
    }
};
