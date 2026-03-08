<?php

namespace Botble\LicenseManager\Tests\Feature\Api\Internal;

use Botble\LicenseManager\Models\Product;

class ActivatedProductControllerTest extends InternalApiTestCase
{
    public function test_store_activates_product(): void
    {
        $product = Product::factory()->inactive()->create();

        $response = $this->postWithHeaders('/api/internal/activated-products/' . $product->getKey());

        $response->assertStatus(200)
            ->assertJsonStructure(['message']);

        $product->refresh();
        $this->assertTrue($product->is_active);
    }

    public function test_destroy_deactivates_product(): void
    {
        $response = $this->deleteWithHeaders('/api/internal/activated-products/' . $this->product->getKey());

        $response->assertStatus(200)
            ->assertJsonStructure(['message']);

        $this->product->refresh();
        $this->assertFalse($this->product->is_active);
    }

    public function test_store_returns_error_for_nonexistent_product(): void
    {
        $response = $this->postWithHeaders('/api/internal/activated-products/99999');

        $response->assertStatus(200)
            ->assertJsonStructure(['message']);
    }

    public function test_destroy_returns_error_for_nonexistent_product(): void
    {
        $response = $this->deleteWithHeaders('/api/internal/activated-products/99999');

        $response->assertStatus(200)
            ->assertJsonStructure(['message']);
    }

    public function test_activate_then_deactivate_roundtrip(): void
    {
        $product = Product::factory()->inactive()->create();

        $this->postWithHeaders('/api/internal/activated-products/' . $product->getKey());
        $product->refresh();
        $this->assertTrue($product->is_active);

        $this->deleteWithHeaders('/api/internal/activated-products/' . $product->getKey());
        $product->refresh();
        $this->assertFalse($product->is_active);
    }
}
