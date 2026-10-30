<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('raffle:health', function (): void {
    $this->info('Raffle Link Platform is ready.');
})->purpose('Check that the raffle application console is available.');
