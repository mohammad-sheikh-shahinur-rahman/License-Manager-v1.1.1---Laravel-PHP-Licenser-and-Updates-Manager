<?php

namespace Botble\LicenseManager\Tests\Feature\Api\External;

use Botble\LicenseManager\Models\ProductActivation;
use Botble\LicenseManager\Models\ProductLicense;
use Carbon\Carbon;

class LicenseActivateTest extends ExternalApiTestCase
{
    protected function activatePayload(array $overrides = []): array
    {
        return array_merge([
            'verify_type' => 'non_envato',
            'product_id' => $this->product->reference_id,
            'license_code' => $this->license->license_code,
            'client_name' => $this->license->customer_id,
        ], $overrides);
    }

    public function test_activates_license_successfully(): void
    {
        $response = $this->postWithHeaders('/api/external/license/activate', $this->activatePayload());

        $response->assertStatus(200)
            ->assertJson(['status' => true, 'is_active' => true]);

        $this->assertNotNull($response->json('lic_response'));

        $this->assertDatabaseHas('lm_activations', [
            'product_reference_id' => $this->product->reference_id,
            'license_code' => $this->license->license_code,
            'is_active' => true,
            'is_valid' => true,
        ]);
    }

    public function test_activates_with_case_insensitive_client_name(): void
    {
        $this->license->update(['customer_id' => 'AnmolSethi']);

        $response = $this->postWithHeaders('/api/external/license/activate', $this->activatePayload([
            'client_name' => 'anmolsethi',
        ]));

        $response->assertStatus(200)
            ->assertJson(['status' => true, 'is_active' => true]);
    }

    public function test_activates_with_uppercase_client_name(): void
    {
        $this->license->update(['customer_id' => 'anmolsethi']);

        $response = $this->postWithHeaders('/api/external/license/activate', $this->activatePayload([
            'client_name' => 'ANMOLSETHI',
        ]));

        $response->assertStatus(200)
            ->assertJson(['status' => true, 'is_active' => true]);
    }

    public function test_activates_with_case_insensitive_license_code(): void
    {
        $this->license->update(['license_code' => 'ABCD-1234-EFGH-5678']);

        $response = $this->postWithHeaders('/api/external/license/activate', $this->activatePayload([
            'license_code' => 'abcd-1234-efgh-5678',
        ]));

        $response->assertStatus(200)
            ->assertJson(['status' => true, 'is_active' => true]);
    }

    public function test_activates_with_mixed_case_license_code(): void
    {
        $this->license->update(['license_code' => 'abcd-1234-efgh-5678']);

        $response = $this->postWithHeaders('/api/external/license/activate', $this->activatePayload([
            'license_code' => 'ABCD-1234-EFGH-5678',
        ]));

        $response->assertStatus(200)
            ->assertJson(['status' => true, 'is_active' => true]);
    }

    public function test_rejects_invalid_license_code(): void
    {
        $response = $this->postWithHeaders('/api/external/license/activate', $this->activatePayload([
            'license_code' => 'invalid-code',
        ]));

        $response->assertStatus(200)
            ->assertJson(['status' => false, 'is_active' => false]);
    }

    public function test_rejects_wrong_client_name(): void
    {
        $response = $this->postWithHeaders('/api/external/license/activate', $this->activatePayload([
            'client_name' => 'wrong-client-name',
        ]));

        $response->assertStatus(200)
            ->assertJson(['status' => false, 'is_active' => false]);
    }

    public function test_rejects_wrong_product_id(): void
    {
        $response = $this->postWithHeaders('/api/external/license/activate', $this->activatePayload([
            'product_id' => 'wrong-product-id',
        ]));

        $response->assertStatus(404);
    }

    public function test_rejects_blocked_license(): void
    {
        $this->license->update(['is_valid' => false]);

        $response = $this->postWithHeaders('/api/external/license/activate', $this->activatePayload());

        $response->assertStatus(200)
            ->assertJson(['status' => false, 'is_active' => false]);
    }

    public function test_rejects_expired_license(): void
    {
        $this->license->update(['expires_at' => Carbon::now()->subDay()]);

        $response = $this->postWithHeaders('/api/external/license/activate', $this->activatePayload());

        $response->assertStatus(200)
            ->assertJson(['status' => false, 'is_active' => false]);
    }

    public function test_rejects_when_max_parallel_uses_reached(): void
    {
        $this->license->update(['parallel_uses' => 1]);

        // Create an existing active activation
        ProductActivation::query()->create([
            'product_reference_id' => $this->product->reference_id,
            'customer_id' => $this->license->customer_id,
            'license_code' => $this->license->license_code,
            'url' => 'https://other-domain.com',
            'ip_address' => '10.0.0.1',
            'activated_at' => Carbon::now(),
            'user_agent' => 'test',
            'is_valid' => true,
            'is_active' => true,
        ]);

        $response = $this->postWithHeaders('/api/external/license/activate', $this->activatePayload());

        $response->assertStatus(200)
            ->assertJson([
                'status' => false,
                'is_active' => false,
                'status_code' => 'ACTIVATED_MAXIMUM_ALLOWED_PRODUCT_INSTANCES',
            ]);
    }

    public function test_allows_activation_when_parallel_uses_not_reached(): void
    {
        $this->license->update(['parallel_uses' => 2]);

        // Create one existing active activation
        ProductActivation::query()->create([
            'product_reference_id' => $this->product->reference_id,
            'customer_id' => $this->license->customer_id,
            'license_code' => $this->license->license_code,
            'url' => 'https://other-domain.com',
            'ip_address' => '10.0.0.1',
            'activated_at' => Carbon::now(),
            'user_agent' => 'test',
            'is_valid' => true,
            'is_active' => true,
        ]);

        $response = $this->postWithHeaders('/api/external/license/activate', $this->activatePayload());

        $response->assertStatus(200)
            ->assertJson(['status' => true, 'is_active' => true]);
    }

    public function test_rejects_domain_restricted_license(): void
    {
        $this->license->update(['domains' => ['allowed-domain.com']]);

        $response = $this->postWithHeaders('/api/external/license/activate', $this->activatePayload());

        $response->assertStatus(200)
            ->assertJson(['status' => false, 'is_active' => false]);
    }

    public function test_rejects_ip_restricted_license(): void
    {
        $this->license->update(['ips' => ['10.0.0.99']]);

        $response = $this->postWithHeaders('/api/external/license/activate', $this->activatePayload());

        $response->assertStatus(200)
            ->assertJson(['status' => false, 'is_active' => false]);
    }

    public function test_records_failed_activation_when_setting_enabled(): void
    {
        setting()->set(['lm_add_entries_for_failed_activation_attempts' => '1'])->save();

        $this->postWithHeaders('/api/external/license/activate', $this->activatePayload([
            'license_code' => 'invalid-code',
        ]));

        $this->assertDatabaseHas('lm_activations', [
            'product_reference_id' => $this->product->reference_id,
            'is_valid' => false,
            'is_active' => false,
        ]);
    }

    public function test_deactivates_old_activations_when_setting_enabled(): void
    {
        setting()->set(['lm_deactivate_old_activations_on_new_activation' => '1'])->save();

        $oldActivation = ProductActivation::query()->create([
            'product_reference_id' => $this->product->reference_id,
            'customer_id' => $this->license->customer_id,
            'license_code' => $this->license->license_code,
            'url' => 'https://old-domain.com',
            'ip_address' => '10.0.0.1',
            'activated_at' => Carbon::now()->subDay(),
            'user_agent' => 'test',
            'is_valid' => true,
            'is_active' => true,
        ]);

        $this->license->update(['parallel_uses' => null]);

        $response = $this->postWithHeaders('/api/external/license/activate', $this->activatePayload());

        $response->assertStatus(200)
            ->assertJson(['status' => true, 'is_active' => true]);

        $oldActivation->refresh();
        $this->assertFalse($oldActivation->is_active);
    }

    public function test_adds_domain_on_first_activation_when_setting_enabled(): void
    {
        setting()->set(['lm_add_domain_of_first_activation_as_licensed_domain' => '1'])->save();

        $this->assertEmpty($this->license->domains);

        $response = $this->postWithHeaders('/api/external/license/activate', $this->activatePayload());

        $response->assertStatus(200)
            ->assertJson(['status' => true, 'is_active' => true]);

        $this->license->refresh();
        $this->assertNotEmpty($this->license->domains);
    }

    public function test_unlimited_parallel_uses_allows_multiple_activations(): void
    {
        $this->license->update(['parallel_uses' => null]);

        ProductActivation::query()->create([
            'product_reference_id' => $this->product->reference_id,
            'customer_id' => $this->license->customer_id,
            'license_code' => $this->license->license_code,
            'url' => 'https://other-domain.com',
            'ip_address' => '10.0.0.1',
            'activated_at' => Carbon::now(),
            'user_agent' => 'test',
            'is_valid' => true,
            'is_active' => true,
        ]);

        $response = $this->postWithHeaders('/api/external/license/activate', $this->activatePayload());

        $response->assertStatus(200)
            ->assertJson(['status' => true, 'is_active' => true]);
    }

    public function test_requires_verify_type(): void
    {
        $response = $this->postWithHeaders('/api/external/license/activate', $this->activatePayload([
            'verify_type' => '',
        ]));

        $response->assertStatus(422);
    }

    public function test_requires_license_code(): void
    {
        $response = $this->postWithHeaders('/api/external/license/activate', [
            'verify_type' => 'non_envato',
            'product_id' => $this->product->reference_id,
            'client_name' => $this->license->customer_id,
        ]);

        $response->assertStatus(422);
    }

    public function test_requires_client_name(): void
    {
        $response = $this->postWithHeaders('/api/external/license/activate', [
            'verify_type' => 'non_envato',
            'product_id' => $this->product->reference_id,
            'license_code' => $this->license->license_code,
        ]);

        $response->assertStatus(422);
    }

    public function test_activates_license_without_customer_id(): void
    {
        $this->license->update(['customer_id' => null]);

        $response = $this->postWithHeaders('/api/external/license/activate', $this->activatePayload([
            'client_name' => 'any-client',
        ]));

        $response->assertStatus(200)
            ->assertJson(['status' => true, 'is_active' => true]);
    }

    public function test_license_code_from_different_product_is_rejected(): void
    {
        $otherLicense = ProductLicense::factory()->create([
            'product_reference_id' => 'other-product-ref',
            'is_valid' => true,
        ]);

        $response = $this->postWithHeaders('/api/external/license/activate', $this->activatePayload([
            'license_code' => $otherLicense->license_code,
            'client_name' => $otherLicense->customer_id,
        ]));

        $response->assertStatus(200)
            ->assertJson(['status' => false, 'is_active' => false]);
    }
}
