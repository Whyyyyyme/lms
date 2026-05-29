<?php

use App\Http\Controllers\Mahasiswa\ChatbotController as StudentChatbotController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active', 'role:mahasiswa'])
    ->prefix('mahasiswa')
    ->name('mahasiswa.')
    ->group(function () {
        Route::get('/chatbot', [StudentChatbotController::class, 'index'])->name('chatbot.index');
    });
