<?php

namespace Botble\LicenseManager\Tests\Feature\Api\External;

class UpdateCheckTest extends ExternalApiTestCase
{
    public function test_returns_update_available_when_newer_version_exists(): void
    {
        $response = $this->postWithHeaders('/api/external/update/check', [
            'product_id' => $this->product->reference_id,
            'current_version' => '0.0.1',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'update_available' => true,
            ])
            ->assertJsonStructure(['version', 'update_id']);
    }

    public function test_returns_no_update_when_on_latest_version(): void
    {
        $response = $this->postWithHeaders('/api/external/update/check', [
            'product_id' => $this->product->reference_id,
            'current_version' => $this->version->version,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'update_available' => false,
            ]);
    }

    public function test_returns_no_update_when_ahead_of_latest(): void
    {
        $response = $this->postWithHeaders('/api/external/update/check', [
            'product_id' => $this->product->reference_id,
            'current_version' => '999.999.999',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'update_available' => false,
            ]);
    }

    public function test_returns_error_for_unknown_product(): void
    {
        $response = $this->postWithHeaders('/api/external/update/check', [
            'product_id' => 'nonexistent-product',
            'current_version' => '1.0.0',
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => false]);
    }

    public function test_returns_no_update_when_no_versions_exist(): void
    {
        $this->version->delete();

        $response = $this->postWithHeaders('/api/external/update/check', [
            'product_id' => $this->product->reference_id,
            'current_version' => '1.0.0',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'update_available' => false,
            ]);
    }

    public function test_ignores_inactive_versions(): void
    {
        $this->version->update(['is_active' => 0]);

        $response = $this->postWithHeaders('/api/external/update/check', [
            'product_id' => $this->product->reference_id,
            'current_version' => '0.0.1',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'update_available' => false,
            ]);
    }

    public function test_requires_product_id_and_current_version(): void
    {
        $response = $this->postWithHeaders('/api/external/update/check', []);

        $response->assertStatus(422);
    }
}
