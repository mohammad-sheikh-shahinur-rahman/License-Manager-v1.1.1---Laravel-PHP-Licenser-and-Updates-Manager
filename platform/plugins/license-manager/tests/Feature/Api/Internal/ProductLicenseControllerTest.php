<?php

namespace Botble\LicenseManager\Tests\Feature\Api\Internal;

use Carbon\Carbon;

class ProductLicenseControllerTest extends InternalApiTestCase
{
    public function test_index_returns_licenses_with_correct_structure(): void
    {
        $response = $this->getWithHeaders('/api/internal/product-licenses?keyword=' . $this->license->license_code);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'product_reference_id',
                        'license_code',
                        'type',
                        'is_envato',
                        'invoice',
                        'customer_id',
                        'email',
                        'comments',
                        'ips',
                        'domains',
                        'support_until',
                        'updates_until',
                        'expires_at',
                        'expiry_days',
                        'uses',
                        'parallel_uses',
                        'is_valid',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    }

    public function test_index_does_not_return_nonexistent_fields(): void
    {
        $response = $this->getWithHeaders('/api/internal/product-licenses?keyword=' . $this->license->license_code);

        $response->assertStatus(200);

        $data = $response->json('data');

        if (! empty($data)) {
            $keys = array_keys($data[0]);
            $this->assertNotContains('product_id', $keys, 'Should use product_reference_id, not product_id');
            $this->assertNotContains('license_type', $keys, 'Should use type, not license_type');
            $this->assertNotContains('ip_addresss', $keys, 'Typo: ip_addresss should not exist');
            $this->assertNotContains('expires_at_days', $keys, 'Should use expiry_days, not expires_at_days');
            $this->assertNotContains('uses_left', $keys, 'uses_left does not exist on model');
        }
    }

    public function test_index_support_until_is_not_domains(): void
    {
        $this->license->update([
            'domains' => ['example.com'],
            'support_until' => Carbon::now()->addYear(),
        ]);

        $response = $this->getWithHeaders('/api/internal/product-licenses?keyword=' . $this->license->license_code);

        $response->assertStatus(200);

        $data = $response->json('data.0');

        $this->assertNotEquals($data['support_until'], $data['domains']);
    }

    public function test_show_returns_license_with_correct_structure(): void
    {
        $response = $this->getWithHeaders('/api/internal/product-licenses/' . $this->license->getKey());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'product_reference_id',
                    'license_code',
                    'type',
                    'is_valid',
                ],
            ]);
    }

    public function test_show_returns_correct_license_data(): void
    {
        $response = $this->getWithHeaders('/api/internal/product-licenses/' . $this->license->getKey());

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $this->license->id,
                'license_code' => $this->license->license_code,
                'product_reference_id' => $this->license->product_reference_id,
                'type' => $this->license->type,
            ]);
    }

    public function test_show_returns_error_for_nonexistent_license(): void
    {
        $response = $this->getWithHeaders('/api/internal/product-licenses/99999');

        $response->assertStatus(200)
            ->assertJsonStructure(['message']);
    }
}
