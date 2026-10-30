<?php

namespace Tests\Feature;

use App\Models\Raffle;
use App\Services\RaffleLinkSigner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PublicRaffleEntryTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_participant_can_register_once_per_raffle(): void
    {
        $raffle = Raffle::query()->create(['name' => 'Public raffle', 'is_active' => true]);
        $signer = app(RaffleLinkSigner::class);
        $token = $signer->generateToken();
        DB::table('raffle_links')->insert([
            'raffle_id' => $raffle->id,
            'token' => $token,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $signature = $signer->sign($token);
        $path = "/r/{$token}.{$signature}/entries";

        $this->from("/r/{$token}.{$signature}")->post($path, [
            'full_name' => 'Test Participant',
            'phone' => '+989121234567',
        ])->assertRedirect(route('raffles.show', [$token, $signature]));

        $this->from("/r/{$token}.{$signature}")->post($path, [
            'full_name' => 'Test Participant',
            'phone' => '+989121234567',
        ])->assertSessionHasErrors('phone');

        $this->assertDatabaseCount('raffle_entries', 1);
    }
}
