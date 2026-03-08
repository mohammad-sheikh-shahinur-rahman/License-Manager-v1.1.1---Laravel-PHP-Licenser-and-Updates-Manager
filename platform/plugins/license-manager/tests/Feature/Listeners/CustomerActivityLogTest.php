<?php

namespace Botble\LicenseManager\Tests\Feature\Listeners;

use Botble\LicenseManager\Events\LicenseActivated;
use Botble\LicenseManager\Events\LicenseDeactivated;
use Botble\LicenseManager\Events\LicenseVerified;
use Botble\LicenseManager\Events\UpdateDownloaded;
use Botble\LicenseManager\Models\CustomerActivityLog;
use Botble\LicenseManager\Models\Product;
use Botble\LicenseManager\Models\ProductActivation;
use Botble\LicenseManager\Models\ProductLicense;
use Botble\LicenseManager\Models\ProductVersion;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CustomerActivityLogTest extends TestCase
{
    use DatabaseTransactions;

    protected Product $product;

    protected ProductLicense $license;

    protected ProductActivation $activation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = Product::factory()->create();
        $this->license = ProductLicense::factory()->create([
            'product_reference_id' => $this->product->reference_id,
        ]);
        $this->activation = ProductActivation::query()->create([
            'product_reference_id' => $this->product->reference_id,
            'customer_id' => $this->license->customer_id,
            'license_code' => $this->license->license_code,
            'url' => 'https://example.com',
            'ip_address' => '127.0.0.1',
            'activated_at' => Carbon::now(),
            'user_agent' => 'TestAgent/1.0',
            'is_valid' => true,
            'is_active' => true,
        ]);
    }

    public function test_license_activated_event_creates_customer_activity_log(): void
    {
        LicenseActivated::dispatch($this->activation, $this->product, $this->license);

        $log = CustomerActivityLog::query()
            ->where('customer_id', $this->license->customer_id)
            ->where('type', 'license_activated')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertStringContainsString($this->license->license_code, $log->message);
        $this->assertStringContainsString('example.com', $log->message);
        $this->assertEquals('127.0.0.1', $log->ip_address);
    }

    public function test_license_deactivated_event_creates_customer_activity_log(): void
    {
        LicenseDeactivated::dispatch($this->activation, $this->product);

        $log = CustomerActivityLog::query()
            ->where('customer_id', $this->license->customer_id)
            ->where('type', 'license_deactivated')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertStringContainsString($this->license->license_code, $log->message);
        $this->assertStringContainsString('example.com', $log->message);
    }

    public function test_license_verified_event_creates_customer_activity_log(): void
    {
        LicenseVerified::dispatch($this->activation, $this->product);

        $log = CustomerActivityLog::query()
            ->where('customer_id', $this->license->customer_id)
            ->where('type', 'license_verified')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertStringContainsString($this->license->license_code, $log->message);
    }

    public function test_update_downloaded_event_creates_customer_activity_log(): void
    {
        $version = ProductVersion::factory()->create([
            'product_reference_id' => $this->product->reference_id,
        ]);

        // Simulate request context with client_name
        $this->app['request']->merge(['client_name' => $this->license->customer_id]);

        UpdateDownloaded::dispatch($version, $this->product, 'main');

        $log = CustomerActivityLog::query()
            ->where('customer_id', $this->license->customer_id)
            ->where('type', 'update_downloaded')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertStringContainsString($version->version, $log->message);
        $this->assertStringContainsString($this->product->name, $log->message);
    }

    public function test_no_customer_activity_log_when_customer_id_is_null(): void
    {
        $activation = ProductActivation::query()->create([
            'product_reference_id' => $this->product->reference_id,
            'customer_id' => null,
            'license_code' => $this->license->license_code,
            'url' => 'https://example.com',
            'ip_address' => '127.0.0.1',
            'activated_at' => Carbon::now(),
            'user_agent' => 'test',
            'is_valid' => true,
            'is_active' => true,
        ]);

        $initialCount = CustomerActivityLog::query()->count();

        LicenseActivated::dispatch($activation, $this->product, $this->license);

        $this->assertEquals($initialCount, CustomerActivityLog::query()->count());
    }

    public function test_activity_log_messages_are_translatable(): void
    {
        LicenseActivated::dispatch($this->activation, $this->product, $this->license);

        $log = CustomerActivityLog::query()
            ->where('type', 'license_activated')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        // Verify the message uses translated format (contains <strong> tags from translation)
        $this->assertStringContainsString('<strong>', $log->message);
        // Verify the translation key resolves (no raw key in output)
        $this->assertStringNotContainsString('activity_log.customer_license_activated', $log->message);
    }
}
