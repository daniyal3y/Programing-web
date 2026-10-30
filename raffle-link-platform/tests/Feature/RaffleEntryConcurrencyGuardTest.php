<?php

namespace Tests\Feature;

use App\Models\Raffle;
use App\Services\RaffleLinkSigner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RaffleEntryConcurrencyGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_database_rejects_duplicate_phone_for_same_raffle(): void
    {
        $raffle = Raffle::query()->create(['name' => 'Test raffle', 'is_active' => true]);
        $token = app(RaffleLinkSigner::class)->generateToken();
        $linkId = DB::table('raffle_links')->insertGetId([
            'raffle_id' => $raffle->id,
            'token' => $token,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $row = [
            'raffle_id' => $raffle->id,
            'raffle_link_id' => $linkId,
            'full_name' => 'Test User',
            'phone' => '+989121234567',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $this->assertSame(1, DB::table('raffle_entries')->insertOrIgnore($row));
        $this->assertSame(0, DB::table('raffle_entries')->insertOrIgnore($row));
        $this->assertDatabaseCount('raffle_entries', 1);
    }
}
