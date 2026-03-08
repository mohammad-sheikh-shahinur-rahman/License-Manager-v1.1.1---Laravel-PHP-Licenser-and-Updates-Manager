<?php

namespace Botble\LicenseManager\Tests\Feature\Api\External;

class UpdateLatestTest extends ExternalApiTestCase
{
    public function test_returns_latest_version(): void
    {
        $response = $this->postWithHeaders('/api/external/update/latest', [
            'product_id' => $this->product->reference_id,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['version', 'update_id', 'product_id']]);

        $this->assertEquals(
            $this->version->version,
            $response->json('data.version')
        );
    }

    public function test_returns_404_for_unknown_product(): void
    {
        $response = $this->postWithHeaders('/api/external/update/latest', [
            'product_id' => 'nonexistent-product',
        ]);

        $response->assertStatus(404);
    }

    public function test_returns_404_when_no_versions_exist(): void
    {
        $this->version->delete();

        $response = $this->postWithHeaders('/api/external/update/latest', [
            'product_id' => $this->product->reference_id,
        ]);

        $response->assertStatus(404);
    }

    public function test_ignores_inactive_versions(): void
    {
        $this->version->update(['is_active' => 0]);

        $response = $this->postWithHeaders('/api/external/update/latest', [
            'product_id' => $this->product->reference_id,
        ]);

        $response->assertStatus(404);
    }

    public function test_requires_product_id(): void
    {
        $response = $this->postWithHeaders('/api/external/update/latest', []);

        $response->assertStatus(422);
    }
}
