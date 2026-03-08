<?php

namespace Botble\LicenseManager\Tests\Feature\Api\Internal;

use Botble\LicenseManager\Models\Product;

class ProductControllerTest extends InternalApiTestCase
{
    public function test_index_returns_active_products(): void
    {
        $response = $this->getWithHeaders('/api/internal/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'reference_id',
                        'envato_id',
                        'description',
                        'license_update',
                        'serve_latest_updates',
                        'is_active',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    }

    public function test_index_excludes_inactive_products(): void
    {
        Product::factory()->inactive()->create();

        $response = $this->getWithHeaders('/api/internal/products');

        $response->assertStatus(200);

        $data = $response->json('data');

        foreach ($data as $product) {
            $this->assertTrue($product['is_active']);
        }
    }

    public function test_index_does_not_return_nonexistent_fields(): void
    {
        $response = $this->getWithHeaders('/api/internal/products');

        $response->assertStatus(200);

        $data = $response->json('data');

        if (! empty($data)) {
            $keys = array_keys($data[0]);
            $this->assertNotContains('unique_id', $keys);
            $this->assertNotContains('is_published', $keys);
            $this->assertNotContains('required_license_for_update', $keys);
        }
    }

    public function test_show_returns_product_with_correct_structure(): void
    {
        $response = $this->getWithHeaders('/api/internal/products/' . $this->product->getKey());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'reference_id',
                    'envato_id',
                    'description',
                    'license_update',
                    'serve_latest_updates',
                    'is_active',
                    'created_at',
                    'updated_at',
                ],
            ]);
    }

    public function test_show_returns_correct_product_data(): void
    {
        $response = $this->getWithHeaders('/api/internal/products/' . $this->product->getKey());

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $this->product->id,
                'name' => $this->product->name,
                'reference_id' => $this->product->reference_id,
            ]);
    }

    public function test_show_returns_error_for_nonexistent_product(): void
    {
        $response = $this->getWithHeaders('/api/internal/products/99999');

        $response->assertStatus(200)
            ->assertJsonStructure(['message']);
    }

    public function test_rejects_missing_api_key(): void
    {
        $response = $this->getJson('/api/internal/products');

        $response->assertStatus(400);
    }

    public function test_rejects_invalid_api_key(): void
    {
        $response = $this->getWithHeaders('/api/internal/products', [
            'X-API-KEY' => 'invalid-key',
        ]);

        $response->assertStatus(401);
    }
}
