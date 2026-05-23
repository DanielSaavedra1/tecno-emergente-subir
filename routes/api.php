<?php

use App\Http\Controllers\Api\V1\ChatMessageController;
use App\Http\Controllers\Api\V1\HealthController;
use Illuminate\Support\Facades\Route;

Route::get('health', HealthController::class)->name('api.v1.health');

Route::middleware(['web', 'auth', 'throttle:ai-chat'])->group(function () {
    Route::post('chat/messages', [ChatMessageController::class, 'store'])->name('api.v1.chat.messages.store');
});
