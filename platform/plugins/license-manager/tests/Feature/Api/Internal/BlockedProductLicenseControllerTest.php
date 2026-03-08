<?php

namespace Botble\LicenseManager\Tests\Feature\Api\Internal;

use Botble\LicenseManager\Models\ProductActivation;
use Carbon\Carbon;

class BlockedProductLicenseControllerTest extends InternalApiTestCase
{
    public function test_store_blocks_license(): void
    {
        $response = $this->postWithHeaders('/api/internal/blocked-product-licenses/' . $this->license->getKey());

        $response->assertStatus(200)
            ->assertJsonStructure(['message']);

        $this->license->refresh();
        $this->assertFalse($this->license->is_valid);
    }

    public function test_destroy_unblocks_license(): void
    {
        $this->license->update(['is_valid' => false]);

        $response = $this->deleteWithHeaders('/api/internal/blocked-product-licenses/' . $this->license->getKey());

        $response->assertStatus(200)
            ->assertJsonStructure(['message']);

        $this->license->refresh();
        $this->assertTrue($this->license->is_valid);
    }

    public function test_blocking_license_deactivates_activations(): void
    {
        $activation = ProductActivation::query()->create([
            'product_reference_id' => $this->product->reference_id,
            'customer_id' => $this->license->customer_id,
            'license_code' => $this->license->license_code,
            'url' => 'https://example.com',
            'ip_address' => '127.0.0.1',
            'activated_at' => Carbon::now(),
            'user_agent' => 'test',
            'is_valid' => true,
            'is_active' => true,
        ]);

        $this->postWithHeaders('/api/internal/blocked-product-licenses/' . $this->license->getKey());

        $activation->refresh();
        $this->assertFalse($activation->is_active);
    }

    public function test_store_returns_error_for_nonexistent_license(): void
    {
        $response = $this->postWithHeaders('/api/internal/blocked-product-licenses/99999');

        $response->assertStatus(200)
            ->assertJsonStructure(['message']);
    }
}
