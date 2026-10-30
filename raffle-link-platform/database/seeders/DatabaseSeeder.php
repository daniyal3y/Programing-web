<?php

namespace Database\Seeders;

use App\Models\Raffle;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Raffle::query()->firstOrCreate(
            ['name' => 'قرعه‌کشی آزمایشی'],
            ['is_active' => true],
        );
    }
}
