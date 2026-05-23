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
        Schema::table('learning_levels', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('position');
        });

        Schema::table('learning_topics', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('learning_levels', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });

        Schema::table('learning_topics', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
