<?php

use App\Http\Controllers\GameRecommendationController;
use Illuminate\Support\Facades\Route;

Route::match(['get', 'post'], '/', [GameRecommendationController::class, 'index']);
Route::get('/game-cover/fallback.svg', [GameRecommendationController::class, 'fallbackCover'])
    ->name('game.cover.fallback');
