<?php

namespace Botble\LicenseManager\Tests\Feature\Api\Internal;

use Botble\LicenseManager\Models\ApiKey;
use Botble\LicenseManager\Models\Product;
use Botble\LicenseManager\Models\ProductLicense;
use Botble\LicenseManager\Models\ProductVersion;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

abstract class InternalApiTestCase extends TestCase
{
    use DatabaseTransactions;

    protected ApiKey $apiKey;

    protected Product $product;

    protected ProductVersion $version;

    protected ProductLicense $license;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiKey = ApiKey::factory()->internal()->special()->create();
        $this->product = Product::factory()->create();
        $this->version = ProductVersion::factory()->create([
            'product_reference_id' => $this->product->reference_id,
        ]);
        $this->license = ProductLicense::factory()->create([
            'product_reference_id' => $this->product->reference_id,
        ]);
    }

    protected function internalHeaders(array $overrides = []): array
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
        return $this->withHeaders($this->internalHeaders($overrides))->getJson($uri);
    }

    protected function postWithHeaders(string $uri, array $data = [], array $headerOverrides = [])
    {
        return $this->withHeaders($this->internalHeaders($headerOverrides))->postJson($uri, $data);
    }

    protected function putWithHeaders(string $uri, array $data = [], array $headerOverrides = [])
    {
        return $this->withHeaders($this->internalHeaders($headerOverrides))->putJson($uri, $data);
    }

    protected function deleteWithHeaders(string $uri, array $headerOverrides = [])
    {
        return $this->withHeaders($this->internalHeaders($headerOverrides))->deleteJson($uri);
    }
}
