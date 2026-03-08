<?php

namespace Botble\LicenseManager\Tests\Feature;

use Botble\LicenseManager\Models\ActivityLog;
use Botble\LicenseManager\Models\Product;
use Botble\LicenseManager\Models\ProductLicense;
use Botble\LicenseManager\Services\BulkLicenseGenerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkLicenseGenerationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BulkLicenseGenerationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(BulkLicenseGenerationService::class);
    }

    public function testGenerateCreatesCorrectNumberOfLicenses(): void
    {
        $product = Product::factory()->create();

        $licenses = $this->service->generate($product, 5);

        $this->assertCount(5, $licenses);
        $this->assertEquals(5, ProductLicense::query()->count());
    }

    public function testGenerateAppliesProductDefaults(): void
    {
        $product = Product::factory()->withLicenseDefaults()->create();

        $this->service->generate($product, 1);

        $license = ProductLicense::query()->first();

        $this->assertEquals('regular', $license->type);
        $this->assertEquals(0, $license->uses);
        $this->assertEquals(3, $license->parallel_uses);
        $this->assertEquals(365, $license->expiry_days);
        $this->assertEquals('Auto-generated license', $license->comments);
    }

    public function testOverridesTakePrecedenceOverDefaults(): void
    {
        $product = Product::factory()->withLicenseDefaults()->create();

        $this->service->generate($product, 1, [
            'type' => 'extended',
            'uses' => 5,
            'parallel_uses' => 10,
        ]);

        $license = ProductLicense::query()->first();

        $this->assertEquals('extended', $license->type);
        $this->assertEquals(5, $license->uses);
        $this->assertEquals(10, $license->parallel_uses);
    }

    public function testComputesExpiresAtFromExpiryDays(): void
    {
        $product = Product::factory()->withLicenseDefaults()->create();

        $this->service->generate($product, 1);

        $license = ProductLicense::query()->first();

        $this->assertNotNull($license->expires_at);
        $this->assertTrue(
            $license->expires_at->isSameDay(now()->addDays(365)),
            'expires_at should be 365 days from now'
        );
    }

    public function testComputesUpdatesUntilFromDaysOffset(): void
    {
        $product = Product::factory()->withLicenseDefaults()->create();

        $this->service->generate($product, 1);

        $license = ProductLicense::query()->first();

        $this->assertNotNull($license->updates_until);
        $this->assertTrue(
            $license->updates_until->isSameDay(now()->addDays(180)),
            'updates_until should be 180 days from now'
        );
    }

    public function testComputesSupportUntilFromDaysOffset(): void
    {
        $product = Product::factory()->withLicenseDefaults()->create();

        $this->service->generate($product, 1);

        $license = ProductLicense::query()->first();

        $this->assertNotNull($license->support_until);
        $this->assertTrue(
            $license->support_until->isSameDay(now()->addDays(90)),
            'support_until should be 90 days from now'
        );
    }

    public function testCreatesUnassignedLicenses(): void
    {
        $product = Product::factory()->create();

        $this->service->generate($product, 1);

        $license = ProductLicense::query()->first();

        $this->assertNull($license->customer_id);
        $this->assertNull($license->email);
    }

    public function testSetsIsEnvatoForEnvatoProduct(): void
    {
        $product = Product::factory()->create(['envato_id' => '12345678']);

        $this->service->generate($product, 1);

        $license = ProductLicense::query()->first();

        $this->assertTrue($license->is_envato);
    }

    public function testDoesNotSetIsEnvatoForNonEnvatoProduct(): void
    {
        $product = Product::factory()->create(['envato_id' => null]);

        $this->service->generate($product, 1);

        $license = ProductLicense::query()->first();

        $this->assertFalse($license->is_envato);
    }

    public function testLogsActivityAfterGeneration(): void
    {
        $product = Product::factory()->create(['name' => 'TestProduct']);

        $this->service->generate($product, 3);

        $log = ActivityLog::query()->latest('id')->first();

        $this->assertNotNull($log);
        $this->assertStringContainsString('3', $log->message);
        $this->assertStringContainsString('TestProduct', $log->message);
    }

    public function testEachLicenseGetsUniqueLicenseCode(): void
    {
        $product = Product::factory()->create();

        $this->service->generate($product, 10);

        $codes = ProductLicense::query()->pluck('license_code')->toArray();

        $this->assertCount(10, array_unique($codes));
    }

    public function testProductWithNoDefaultsStillCreatesLicenses(): void
    {
        $product = Product::factory()->create();

        $licenses = $this->service->generate($product, 3);

        $this->assertCount(3, $licenses);

        $license = ProductLicense::query()->first();

        $this->assertNull($license->type);
        $this->assertEquals(0, $license->uses);
        $this->assertNull($license->expires_at);
    }
}
