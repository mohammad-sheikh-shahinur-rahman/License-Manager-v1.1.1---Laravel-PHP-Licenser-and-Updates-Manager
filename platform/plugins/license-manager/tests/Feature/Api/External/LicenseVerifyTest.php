<?php

namespace Botble\LicenseManager\Tests\Feature\Api\External;

use Botble\LicenseManager\LicenseManager;
use Botble\LicenseManager\Models\ProductActivation;
use Carbon\Carbon;

class LicenseVerifyTest extends ExternalApiTestCase
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

    public function test_verifies_valid_license(): void
    {
        $activation = $this->createActivation();

        $licenseData = $this->encryptLicenseData([
            'id' => $activation->getKey(),
            'activation_id' => $activation->id,
        ]);

        $response = $this->postWithHeaders('/api/external/license/verify', [
            'product_id' => $this->product->reference_id,
            'license_data' => $licenseData,
            'client_name' => $this->license->customer_id,
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => true, 'is_active' => true]);
    }

    public function test_verifies_with_case_insensitive_client_name(): void
    {
        $activation = $this->createActivation(['customer_id' => 'AnmolSethi']);

        $licenseData = $this->encryptLicenseData([
            'id' => $activation->getKey(),
            'activation_id' => $activation->id,
        ]);

        $response = $this->postWithHeaders('/api/external/license/verify', [
            'product_id' => $this->product->reference_id,
            'license_data' => $licenseData,
            'client_name' => 'anmolsethi',
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => true, 'is_active' => true]);
    }

    public function test_verifies_with_uppercase_client_name(): void
    {
        $activation = $this->createActivation(['customer_id' => 'anmolsethi']);

        $licenseData = $this->encryptLicenseData([
            'id' => $activation->getKey(),
            'activation_id' => $activation->id,
        ]);

        $response = $this->postWithHeaders('/api/external/license/verify', [
            'product_id' => $this->product->reference_id,
            'license_data' => $licenseData,
            'client_name' => 'ANMOLSETHI',
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => true, 'is_active' => true]);
    }

    public function test_rejects_wrong_client_name(): void
    {
        $activation = $this->createActivation(['customer_id' => 'AnmolSethi']);

        $licenseData = $this->encryptLicenseData([
            'id' => $activation->getKey(),
            'activation_id' => $activation->id,
        ]);

        $response = $this->postWithHeaders('/api/external/license/verify', [
            'product_id' => $this->product->reference_id,
            'license_data' => $licenseData,
            'client_name' => 'DifferentUser',
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => false, 'is_active' => false]);
    }

    public function test_rejects_invalid_license_data(): void
    {
        $response = $this->postWithHeaders('/api/external/license/verify', [
            'product_id' => $this->product->reference_id,
            'license_data' => 'invalid-encrypted-data',
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => false, 'is_active' => false]);
    }

    public function test_rejects_deactivated_license(): void
    {
        $activation = $this->createActivation(['is_active' => false]);

        $licenseData = $this->encryptLicenseData([
            'id' => $activation->getKey(),
            'activation_id' => $activation->id,
        ]);

        $response = $this->postWithHeaders('/api/external/license/verify', [
            'product_id' => $this->product->reference_id,
            'license_data' => $licenseData,
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => false, 'is_active' => false]);
    }

    public function test_rejects_invalid_product_id(): void
    {
        $response = $this->postWithHeaders('/api/external/license/verify', [
            'product_id' => 'wrong-product',
            'license_data' => 'some-data',
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => false, 'is_active' => false]);
    }

    public function test_rejects_wrong_product_for_activation(): void
    {
        $activation = $this->createActivation([
            'product_reference_id' => 'other-product-ref',
        ]);

        $licenseData = $this->encryptLicenseData([
            'id' => $activation->getKey(),
            'activation_id' => $activation->id,
        ]);

        $response = $this->postWithHeaders('/api/external/license/verify', [
            'product_id' => $this->product->reference_id,
            'license_data' => $licenseData,
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => false, 'is_active' => false]);
    }

    public function test_rejects_domain_mismatch(): void
    {
        $activation = $this->createActivation(['url' => 'https://other-domain.com']);

        $licenseData = $this->encryptLicenseData([
            'id' => $activation->getKey(),
            'activation_id' => $activation->id,
        ]);

        $response = $this->postWithHeaders('/api/external/license/verify', [
            'product_id' => $this->product->reference_id,
            'license_data' => $licenseData,
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => false, 'is_active' => false]);
    }

    public function test_ip_verification_passes_with_same_ip(): void
    {
        setting()->set(['lm_verify_license_ip' => true])->save();

        $activation = $this->createActivation(['ip_address' => '127.0.0.1']);

        $licenseData = $this->encryptLicenseData([
            'id' => $activation->getKey(),
            'activation_id' => $activation->id,
        ]);

        $response = $this->postWithHeaders('/api/external/license/verify', [
            'product_id' => $this->product->reference_id,
            'license_data' => $licenseData,
            'client_name' => $this->license->customer_id,
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => true, 'is_active' => true]);
    }

    public function test_ip_verification_fails_with_different_ip(): void
    {
        setting()->set(['lm_verify_license_ip' => true])->save();

        $activation = $this->createActivation(['ip_address' => '10.0.0.1']);

        $licenseData = $this->encryptLicenseData([
            'id' => $activation->getKey(),
            'activation_id' => $activation->id,
        ]);

        $response = $this->postWithHeaders('/api/external/license/verify', [
            'product_id' => $this->product->reference_id,
            'license_data' => $licenseData,
            'client_name' => $this->license->customer_id,
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => false, 'is_active' => false]);
    }

    public function test_ip_verification_matches_mapped_ipv6_to_ipv4(): void
    {
        setting()->set(['lm_verify_license_ip' => true])->save();

        $activation = $this->createActivation(['ip_address' => '::ffff:127.0.0.1']);

        $licenseData = $this->encryptLicenseData([
            'id' => $activation->getKey(),
            'activation_id' => $activation->id,
        ]);

        $response = $this->postWithHeaders('/api/external/license/verify', [
            'product_id' => $this->product->reference_id,
            'license_data' => $licenseData,
            'client_name' => $this->license->customer_id,
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => true, 'is_active' => true]);
    }

    public function test_ip_verification_matches_expanded_ipv6_loopback(): void
    {
        setting()->set(['lm_verify_license_ip' => true])->save();

        $activation = $this->createActivation(['ip_address' => '0:0:0:0:0:0:0:1']);

        $licenseData = $this->encryptLicenseData([
            'id' => $activation->getKey(),
            'activation_id' => $activation->id,
        ]);

        $response = $this->postWithHeaders('/api/external/license/verify', [
            'product_id' => $this->product->reference_id,
            'license_data' => $licenseData,
            'client_name' => $this->license->customer_id,
        ], ['X-API-IP' => '::1']);

        $response->assertStatus(200)
            ->assertJson(['status' => true, 'is_active' => true]);
    }

    public function test_ip_verification_skipped_when_disabled(): void
    {
        setting()->set(['lm_verify_license_ip' => false])->save();

        $activation = $this->createActivation(['ip_address' => '10.0.0.1']);

        $licenseData = $this->encryptLicenseData([
            'id' => $activation->getKey(),
            'activation_id' => $activation->id,
        ]);

        $response = $this->postWithHeaders('/api/external/license/verify', [
            'product_id' => $this->product->reference_id,
            'license_data' => $licenseData,
            'client_name' => $this->license->customer_id,
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => true, 'is_active' => true]);
    }

    public function test_domain_normalization_treats_www_as_equivalent_when_enabled(): void
    {
        setting()->set(['lm_normalize_domain_variants' => true])->save();

        $activation = $this->createActivation(['url' => 'https://www.example.com']);

        $licenseData = $this->encryptLicenseData([
            'id' => $activation->getKey(),
            'activation_id' => $activation->id,
        ]);

        $response = $this->postWithHeaders('/api/external/license/verify', [
            'product_id' => $this->product->reference_id,
            'license_data' => $licenseData,
            'client_name' => $this->license->customer_id,
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => true, 'is_active' => true]);
    }

    public function test_domain_normalization_rejects_www_mismatch_when_disabled(): void
    {
        setting()->set(['lm_normalize_domain_variants' => false])->save();

        $activation = $this->createActivation(['url' => 'https://www.example.com']);

        $licenseData = $this->encryptLicenseData([
            'id' => $activation->getKey(),
            'activation_id' => $activation->id,
        ]);

        $response = $this->postWithHeaders('/api/external/license/verify', [
            'product_id' => $this->product->reference_id,
            'license_data' => $licenseData,
            'client_name' => $this->license->customer_id,
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

        $response = $this->postWithHeaders('/api/external/license/verify', [
            'product_id' => $this->product->reference_id,
            'license_file' => $licenseData,
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => true, 'is_active' => true]);
    }
}
