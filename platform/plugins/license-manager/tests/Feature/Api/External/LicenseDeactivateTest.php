<?php

namespace Botble\LicenseManager\Tests\Feature\Api\External;

use Botble\LicenseManager\LicenseManager;
use Botble\LicenseManager\Models\ProductActivation;
use Carbon\Carbon;

class LicenseDeactivateTest extends ExternalApiTestCase
{
    protected string $encryptionKey;

    protected function setUp(): void
    {
        parent::setUp();

        $this->encryptionKey = 'base64:' . base64_encode(random_bytes(16));

        setting()->set([
            'lm_license_encryption_key' => $this->encryptionKey,
            'lm_license_encryption_cipher' => 'aes-128-cbc',
        ])->save();
    }

    protected function createActivation(array $overrides = []): ProductActivation
    {
        return ProductActivation::query()->create(array_merge([
            'product_reference_id' => $this->product->reference_id,
            'customer_id' => $this->license->customer_id,
            'license_code' => $this->license->license_code,
            'url' => 'https://example.com',
            'ip_address' => '127.0.0.1',
            'activated_at' => Carbon::now(),
            'user_agent' => 'test',
            'is_valid' => true,
            'is_active' => true,
        ], $overrides));
    }

    protected function encryptLicenseData(array $data): string
    {
        return app(LicenseManager::class)->createEncrypter()->encrypt($data);
    }

    public function test_deactivates_license_with_license_data(): void
    {
        $activation = $this->createActivation();

        $licenseData = $this->encryptLicenseData([
            'id' => $activation->getKey(),
            'activation_id' => $activation->id,
        ]);

        $response = $this->postWithHeaders('/api/external/license/deactivate', [
            'product_id' => $this->product->reference_id,
            'license_data' => $licenseData,
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => true]);

        $activation->refresh();
        $this->assertFalse($activation->is_active);
    }

    public function test_deactivates_with_case_insensitive_license_code(): void
    {
        $this->license->update(['license_code' => 'ABCD-1234-EFGH-5678']);
        $activation = $this->createActivation(['license_code' => 'ABCD-1234-EFGH-5678']);

        $response = $this->postWithHeaders('/api/external/license/deactivate', [
            'product_id' => $this->product->reference_id,
            'license_code' => 'abcd-1234-efgh-5678',
            'client_name' => $this->license->customer_id,
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => true]);

        $activation->refresh();
        $this->assertFalse($activation->is_active);
    }

    public function test_deactivates_with_case_insensitive_client_name(): void
    {
        $activation = $this->createActivation(['customer_id' => 'AnmolSethi']);

        $licenseData = $this->encryptLicenseData([
            'id' => $activation->getKey(),
            'activation_id' => $activation->id,
        ]);

        $response = $this->postWithHeaders('/api/external/license/deactivate', [
            'product_id' => $this->product->reference_id,
            'license_data' => $licenseData,
            'client_name' => 'anmolsethi',
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => true]);

        $activation->refresh();
        $this->assertFalse($activation->is_active);
    }

    public function test_rejects_invalid_license_data(): void
    {
        $response = $this->postWithHeaders('/api/external/license/deactivate', [
            'product_id' => $this->product->reference_id,
            'license_data' => 'invalid-data',
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => false, 'is_active' => false]);
    }

    public function test_rejects_wrong_product_id(): void
    {
        $response = $this->postWithHeaders('/api/external/license/deactivate', [
            'product_id' => 'wrong-product',
            'license_data' => 'some-data',
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => false, 'is_active' => false]);
    }

    public function test_supports_legacy_license_file_field(): void
    {
        $activation = $this->createActivation();

        $licenseData = $this->encryptLicenseData([
            'id' => $activation->getKey(),
            'activation_id' => $activation->id,
        ]);

        $response = $this->postWithHeaders('/api/external/license/deactivate', [
            'product_id' => $this->product->reference_id,
            'license_file' => $licenseData,
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => true]);

        $activation->refresh();
        $this->assertFalse($activation->is_active);
    }

    public function test_deactivates_with_license_code_and_client_name(): void
    {
        $activation = $this->createActivation();

        $response = $this->postWithHeaders('/api/external/license/deactivate', [
            'product_id' => $this->product->reference_id,
            'license_code' => $this->license->license_code,
            'client_name' => $this->license->customer_id,
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => true]);

        $activation->refresh();
        $this->assertFalse($activation->is_active);
    }

    public function test_requires_license_data_or_code(): void
    {
        $response = $this->postWithHeaders('/api/external/license/deactivate', [
            'product_id' => $this->product->reference_id,
        ]);

        $response->assertStatus(422);
    }
}
