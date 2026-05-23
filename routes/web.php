<?php

use App\Http\Controllers\Web\WorkspaceController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('workspace', WorkspaceController::class)->name('workspace.index');
    Route::get('workspace/exercises/{exercise}', [WorkspaceController::class, 'showExercise'])
        ->name('workspace.exercises.show');
    Route::post('workspace/run-code', [WorkspaceController::class, 'runCode'])
        ->middleware('throttle:workspace-run-code')
        ->name('workspace.run-code');
});

require __DIR__.'/settings.php';
