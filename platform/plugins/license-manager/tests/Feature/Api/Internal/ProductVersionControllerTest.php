<?php

namespace Botble\LicenseManager\Tests\Feature\Api\Internal;

class ProductVersionControllerTest extends InternalApiTestCase
{
    public function test_index_returns_versions_with_correct_structure(): void
    {
        $response = $this->getWithHeaders('/api/internal/products/' . $this->product->reference_id . '/versions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'version_id',
                        'product_reference_id',
                        'version',
                        'released_at',
                        'summary',
                        'changelog',
                        'main_file',
                        'sql_file',
                        'is_active',
                    ],
                ],
            ]);
    }

    public function test_index_does_not_return_nonexistent_fields(): void
    {
        $response = $this->getWithHeaders('/api/internal/products/' . $this->product->reference_id . '/versions');

        $response->assertStatus(200);

        $data = $response->json('data');

        if (! empty($data)) {
            $keys = array_keys($data[0]);
            $this->assertNotContains('version_status', $keys);
            $this->assertNotContains('reference_id', $keys, 'Should use product_reference_id, not reference_id');
        }
    }

    public function test_index_returns_correct_product_reference_id(): void
    {
        $response = $this->getWithHeaders('/api/internal/products/' . $this->product->reference_id . '/versions');

        $response->assertStatus(200);

        $data = $response->json('data');

        if (! empty($data)) {
            $this->assertEquals($this->version->product_reference_id, $data[0]['product_reference_id']);
        }
    }

    public function test_show_returns_version_with_correct_structure(): void
    {
        $response = $this->getWithHeaders(
            '/api/internal/products/' . $this->product->reference_id . '/versions/' . $this->version->version_id
        );

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'version_id',
                    'product_reference_id',
                    'version',
                    'released_at',
                    'summary',
                    'changelog',
                    'main_file',
                    'sql_file',
                    'is_active',
                ],
            ]);
    }

    public function test_show_returns_error_for_nonexistent_version(): void
    {
        $response = $this->getWithHeaders(
            '/api/internal/products/' . $this->product->reference_id . '/versions/nonexistent'
        );

        $response->assertStatus(200)
            ->assertJsonStructure(['message']);
    }

    public function test_index_returns_error_for_nonexistent_product(): void
    {
        $response = $this->getWithHeaders('/api/internal/products/nonexistent/versions');

        $response->assertStatus(200)
            ->assertJsonStructure(['message']);
    }
}
