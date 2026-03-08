<?php

namespace Botble\LicenseManager\Tests\Feature\Api\External;

use Botble\LicenseManager\Models\ApiKey;
use Botble\LicenseManager\Models\Product;
use Botble\LicenseManager\Models\ProductLicense;
use Botble\LicenseManager\Models\ProductVersion;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

abstract class ExternalApiTestCase extends TestCase
{
    use DatabaseTransactions;

    protected ApiKey $apiKey;

    protected Product $product;

    protected ProductVersion $version;

    protected ProductLicense $license;

    protected function setUp(): void
    {
        parent::setUp();

        setting()->set([
            'lm_license_encryption_key' => 'base64:' . base64_encode(random_bytes(16)),
            'lm_license_encryption_cipher' => 'aes-128-cbc',
        ])->save();

        $this->apiKey = ApiKey::factory()->special()->create();
        $this->product = Product::factory()->create([
            'is_active' => 1,
            'license_update' => 0,
        ]);
        $this->version = ProductVersion::factory()->create([
            'product_reference_id' => $this->product->reference_id,
        ]);
        $this->license = ProductLicense::factory()->create([
            'product_reference_id' => $this->product->reference_id,
            'is_valid' => true,
        ]);
    }

    protected function externalHeaders(array $overrides = []): array
    {
        return array_merge([
            'X-API-KEY' => $this->apiKey->key,
            'X-API-URL' => 'https://example.com',
            'X-API-IP' => '127.0.0.1',
            'X-API-LANGUAGE' => 'en',
        ], $overrides);
    }

    protected function getWithHeaders(string $uri, array $overrides = [])
    {
        return $this->withHeaders($this->externalHeaders($overrides))->get($uri);
    }

    protected function postWithHeaders(string $uri, array $data = [], array $headerOverrides = [])
    {
        return $this->withHeaders($this->externalHeaders($headerOverrides))->post($uri, $data);
    }

    protected function createTestFile(string $filename, string $content = 'test-content'): string
    {
        $path = storage_path('app/version-files/' . $this->product->reference_id);

        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }

        $filePath = $path . '/' . $filename;
        file_put_contents($filePath, $content);

        return $filePath;
    }

    protected function cleanupTestFiles(): void
    {
        $path = storage_path('app/version-files/' . $this->product->reference_id);

        if (is_dir($path)) {
            array_map('unlink', glob($path . '/*'));
            rmdir($path);
        }
    }

    protected function tearDown(): void
    {
        $this->cleanupTestFiles();
        parent::tearDown();
    }
}
