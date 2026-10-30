<?php

use App\Http\Controllers\Api\Admin\LinkController;
use App\Http\Controllers\Api\Admin\RaffleController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->middleware(['admin.token', 'throttle:admin-api'])
    ->name('api.admin.')
    ->group(function (): void {
        Route::post('/raffles', [RaffleController::class, 'store'])->name('raffles.store');
        Route::post('/raffles/{raffle}/links', [LinkController::class, 'store'])
            ->whereNumber('raffle')
            ->name('raffles.links.store');
    });
