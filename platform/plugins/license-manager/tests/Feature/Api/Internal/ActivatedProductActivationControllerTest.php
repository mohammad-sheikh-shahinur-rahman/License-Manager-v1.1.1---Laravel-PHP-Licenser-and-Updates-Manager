<?php

namespace Botble\LicenseManager\Tests\Feature\Api\Internal;

use Botble\LicenseManager\Models\ProductActivation;
use Carbon\Carbon;

class ActivatedProductActivationControllerTest extends InternalApiTestCase
{
    protected ProductActivation $activation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->activation = ProductActivation::query()->create([
            'product_reference_id' => $this->product->reference_id,
            'customer_id' => $this->license->customer_id,
            'license_code' => $this->license->license_code,
            'url' => 'https://example.com',
            'ip_address' => '127.0.0.1',
            'activated_at' => Carbon::now(),
            'user_agent' => 'test',
            'is_valid' => true,
            'is_active' => false,
        ]);
    }

    public function test_store_activates_activation(): void
    {
        $response = $this->postWithHeaders('/api/internal/activated-product-activations/' . $this->activation->getKey());

        $response->assertStatus(200)
            ->assertJsonStructure(['message']);

        $this->activation->refresh();
        $this->assertTrue($this->activation->is_active);
    }

    public function test_destroy_deactivates_activation(): void
    {
        $this->activation->update(['is_active' => true]);

        $response = $this->deleteWithHeaders('/api/internal/activated-product-activations/' . $this->activation->getKey());

        $response->assertStatus(200)
            ->assertJsonStructure(['message']);

        $this->activation->refresh();
        $this->assertFalse($this->activation->is_active);
    }

    public function test_store_returns_error_for_nonexistent_activation(): void
    {
        $response = $this->postWithHeaders('/api/internal/activated-product-activations/99999');

        $response->assertStatus(200)
            ->assertJsonStructure(['message']);
    }

    public function test_destroy_returns_error_for_nonexistent_activation(): void
    {
        $response = $this->deleteWithHeaders('/api/internal/activated-product-activations/99999');

        $response->assertStatus(200)
            ->assertJsonStructure(['message']);
    }
}
