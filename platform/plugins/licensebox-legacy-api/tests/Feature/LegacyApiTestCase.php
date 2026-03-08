<?php

namespace Botble\LegacyApi\Tests\Feature;

use Botble\LicenseManager\Models\ApiKey;
use Botble\LicenseManager\Models\Product;
use Botble\LicenseManager\Models\ProductLicense;
use Botble\LicenseManager\Models\ProductVersion;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

abstract class LegacyApiTestCase extends TestCase
{
    use DatabaseTransactions;

    protected ApiKey $apiKey;

    protected Product $product;

    protected ProductVersion $version;

    protected ProductLicense $license;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiKey = ApiKey::factory()->special()->create();
        $this->product = Product::factory()->create([
            'reference_id' => 'TESTPROD',
            'name' => 'Test Product',
            'is_active' => 1,
            'license_update' => 0,
        ]);
        $this->version = ProductVersion::factory()->create([
            'product_reference_id' => $this->product->reference_id,
            'version' => '1.0.0',
        ]);
        $this->license = ProductLicense::factory()->create([
            'product_reference_id' => $this->product->reference_id,
            'is_valid' => true,
            'uses' => 0,
            'parallel_uses' => 1,
        ]);
    }

    protected function legacyHeaders(array $overrides = []): array
    {
        return array_merge([
            'LB-API-KEY' => $this->apiKey->key,
            'LB-URL' => 'https://example.com',
            'LB-IP' => '127.0.0.1',
            'LB-LANG' => 'english',
        ], $overrides);
    }

    protected function postLegacy(string $uri, array $data = [], array $headerOverrides = [])
    {
        return $this->withHeaders($this->legacyHeaders($headerOverrides))->post($uri, $data);
    }

    protected function internalHeaders(array $overrides = []): array
    {
        return array_merge([
            'LB-API-KEY' => $this->apiKey->key,
            'X-API-URL' => 'https://example.com',
            'X-API-IP' => '127.0.0.1',
            'X-API-LANGUAGE' => 'en',
        ], $overrides);
    }

    protected function postInternal(string $uri, array $data = [], array $headerOverrides = [])
    {
        return $this->withHeaders($this->internalHeaders($headerOverrides))->post($uri, $data);
    }
}
