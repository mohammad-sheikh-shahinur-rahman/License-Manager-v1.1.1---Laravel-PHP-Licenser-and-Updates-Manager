<?php

namespace Botble\LicenseManager\Tests\Feature\Api\External;

use Carbon\Carbon;

class ConnectionCheckTest extends ExternalApiTestCase
{
    public function test_returns_success_status(): void
    {
        $response = $this->getWithHeaders('/api/external/connection-check');

        $response->assertStatus(200)
            ->assertJson(['status' => true, 'is_active' => true]);
    }

    public function test_rejects_missing_api_key(): void
    {
        $response = $this->get('/api/external/connection-check');

        $response->assertStatus(400);
    }

    public function test_rejects_invalid_api_key(): void
    {
        $response = $this->getWithHeaders('/api/external/connection-check', [
            'X-API-KEY' => 'invalid-key',
        ]);

        $response->assertStatus(401);
    }

    public function test_rejects_revoked_api_key(): void
    {
        $this->apiKey->update(['revoked' => true]);

        $response = $this->getWithHeaders('/api/external/connection-check');

        $response->assertStatus(401);
    }

    public function test_rejects_expired_api_key(): void
    {
        $this->apiKey->update(['expires_at' => Carbon::now()->subDay()]);

        $response = $this->getWithHeaders('/api/external/connection-check');

        $response->assertStatus(401);
    }

    public function test_accepts_valid_api_key_not_yet_expired(): void
    {
        $this->apiKey->update(['expires_at' => Carbon::now()->addDay()]);

        $response = $this->getWithHeaders('/api/external/connection-check');

        $response->assertStatus(200);
    }
}
