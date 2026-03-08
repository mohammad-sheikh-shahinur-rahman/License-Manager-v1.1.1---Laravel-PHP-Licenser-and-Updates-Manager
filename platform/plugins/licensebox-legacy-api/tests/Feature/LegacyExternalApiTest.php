<?php

namespace Botble\LegacyApi\Tests\Feature;

class LegacyExternalApiTest extends LegacyApiTestCase
{
    public function test_check_connection_ext(): void
    {
        $response = $this->postLegacy('/api/check_connection_ext');

        $response->assertOk()->assertJson(['status' => true]);
    }

    public function test_check_connection_ext_rejects_missing_api_key(): void
    {
        $response = $this->post('/api/check_connection_ext');

        $response->assertStatus(400);
    }

    public function test_check_connection_ext_rejects_invalid_api_key(): void
    {
        $response = $this->postLegacy('/api/check_connection_ext', [], [
            'LB-API-KEY' => 'invalid-key',
        ]);

        $response->assertStatus(401);
    }

    public function test_activate_license_returns_expected_structure(): void
    {
        $response = $this->postLegacy('/api/activate_license', [
            'product_id' => $this->product->reference_id,
            'license_code' => $this->license->license_code,
            'client_name' => 'Test Client',
            'verify_type' => 'non_envato',
        ]);

        $response->assertJsonStructure(['status', 'message']);
    }

    public function test_activate_license_rejects_missing_verify_type(): void
    {
        $response = $this->postLegacy('/api/activate_license', [
            'product_id' => $this->product->reference_id,
            'license_code' => $this->license->license_code,
        ]);

        $response->assertStatus(422);
    }

    public function test_verify_license(): void
    {
        $response = $this->postLegacy('/api/verify_license', [
            'product_id' => $this->product->reference_id,
            'license_data' => 'test-license-data',
        ]);

        $response->assertOk()->assertJsonStructure(['status']);
    }

    public function test_deactivate_license(): void
    {
        $response = $this->postLegacy('/api/deactivate_license', [
            'product_id' => $this->product->reference_id,
            'license_data' => 'test-license-data',
        ]);

        $response->assertOk()->assertJsonStructure(['status']);
    }

    public function test_check_update(): void
    {
        $response = $this->postLegacy('/api/check_update', [
            'product_id' => $this->product->reference_id,
            'current_version' => '0.9.0',
        ]);

        $response->assertOk()->assertJsonStructure(['status']);
    }

    public function test_latest_version(): void
    {
        $response = $this->postLegacy('/api/latest_version', [
            'product_id' => $this->product->reference_id,
        ]);

        $response->assertOk()->assertJsonStructure(['data']);
    }
}
