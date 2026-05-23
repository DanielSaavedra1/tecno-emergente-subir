<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('exercises')
            ->select('slug')
            ->groupBy('slug')
            ->havingRaw('COUNT(*) > 1')
            ->orderBy('slug')
            ->pluck('slug')
            ->each(function (string $slug): void {
                $duplicateExercises = DB::table('exercises')
                    ->where('slug', $slug)
                    ->orderByDesc('is_active')
                    ->orderBy('id')
                    ->get(['id', 'slug']);

                $duplicateExercises->skip(1)->each(function (object $exercise): void {
                    DB::table('exercises')
                        ->where('id', $exercise->id)
                        ->update(['slug' => $exercise->slug.'-'.$exercise->id]);
                });
            });

        Schema::table('exercises', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exercises', function (Blueprint $table) {
            $table->dropUnique(['slug']);
        });
    }
};
