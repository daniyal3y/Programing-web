<?php

use App\Http\Controllers\RaffleLandingController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->view('welcome'))->name('home');

Route::get('/r/{token}.{signature}', [RaffleLandingController::class, 'show'])
    ->where(['token' => '[A-Za-z0-9_-]{22}', 'signature' => '[a-f0-9]{32}'])
    ->name('raffles.show');

Route::post('/r/{token}.{signature}/entries', [RaffleLandingController::class, 'store'])
    ->where(['token' => '[A-Za-z0-9_-]{22}', 'signature' => '[a-f0-9]{32}'])
    ->middleware('throttle:raffle-entry')
    ->name('raffles.entries.store');
