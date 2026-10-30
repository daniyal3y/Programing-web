<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_raffle_and_issue_a_signed_link(): void
    {
        $response = $this->withToken('test-admin-token')->postJson('/api/admin/raffles', [
            'name' => 'Autumn raffle',
            'is_active' => true,
        ]);

        $response->assertCreated()->assertJsonPath('data.name', 'Autumn raffle');
        $raffleId = $response->json('data.id');

        $links = $this->withToken('test-admin-token')->postJson("/api/admin/raffles/{$raffleId}/links", [
            'quantity' => 3,
        ]);

        $links->assertCreated()->assertJsonPath('data.issued', 3);
        $this->assertMatchesRegularExpression('/\/r\/[A-Za-z0-9_-]{22}\.[a-f0-9]{32}$/', $links->json('data.links.0.url'));
    }

    public function test_admin_api_requires_a_bearer_token(): void
    {
        $this->postJson('/api/admin/raffles', ['name' => 'No access'])->assertUnauthorized();
    }
}
