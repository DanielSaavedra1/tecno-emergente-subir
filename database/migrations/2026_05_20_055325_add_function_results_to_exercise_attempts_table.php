<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exercise_attempts', function (Blueprint $table) {
            $table->json('function_results')->nullable()->after('compile_output');
            $table->boolean('function_results_passed')->nullable()->after('function_results');
        });
    }

    public function down(): void
    {
        Schema::table('exercise_attempts', function (Blueprint $table) {
            $table->dropColumn(['function_results', 'function_results_passed']);
        });
    }
};
