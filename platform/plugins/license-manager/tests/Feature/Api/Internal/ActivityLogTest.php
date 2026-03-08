<?php

namespace Botble\LicenseManager\Tests\Feature\Api\Internal;

use Botble\LicenseManager\Models\ActivityLog;
use Botble\LicenseManager\Models\Customer;
use Botble\LicenseManager\Models\ProductActivation;
use Carbon\Carbon;

class ActivityLogTest extends InternalApiTestCase
{
    private function assertNewLogContains(callable $action, string $expectedContent): void
    {
        $beforeCount = ActivityLog::query()->count();

        $action();

        $newLogs = ActivityLog::query()
            ->latest('created_at')
            ->limit(ActivityLog::query()->count() - $beforeCount)
            ->get();

        $this->assertGreaterThan($beforeCount, ActivityLog::query()->count(), 'Expected a new ActivityLog entry to be created.');

        $found = $newLogs->first(fn (ActivityLog $log) => str_contains($log->message, $expectedContent));
        $this->assertNotNull($found, "Expected a new ActivityLog message containing '{$expectedContent}', but none found.");
    }

    public function test_blocking_license_creates_activity_log(): void
    {
        $this->assertNewLogContains(
            fn () => $this->postWithHeaders('/api/internal/blocked-product-licenses/' . $this->license->getKey()),
            $this->license->license_code
        );
    }

    public function test_unblocking_license_creates_activity_log(): void
    {
        $this->license->update(['is_valid' => false]);

        $this->assertNewLogContains(
            fn () => $this->deleteWithHeaders('/api/internal/blocked-product-licenses/' . $this->license->getKey()),
            $this->license->license_code
        );
    }

    public function test_creating_product_via_api_creates_activity_log(): void
    {
        $this->assertNewLogContains(
            fn () => $this->postWithHeaders('/api/internal/products', [
                'name' => 'Test Product',
                'reference_id' => 'TEST-PROD-' . uniqid(),
                'is_active' => true,
            ]),
            'Test Product'
        );
    }

    public function test_updating_product_via_api_creates_activity_log(): void
    {
        $this->assertNewLogContains(
            fn () => $this->putWithHeaders('/api/internal/products/' . $this->product->getKey(), [
                'name' => 'Updated Product Name',
                'is_active' => true,
            ]),
            'Updated Product Name'
        );
    }

    public function test_creating_license_via_api_creates_activity_log(): void
    {
        $licenseCode = (string) \Illuminate\Support\Str::uuid();

        $this->assertNewLogContains(
            fn () => $this->postWithHeaders('/api/internal/product-licenses', [
                'product_reference_id' => $this->product->reference_id,
                'license_code' => $licenseCode,
                'customer_id' => 'TEST-CUST',
            ]),
            $licenseCode
        );
    }

    public function test_updating_license_via_api_creates_activity_log(): void
    {
        $this->assertNewLogContains(
            fn () => $this->putWithHeaders('/api/internal/product-licenses/' . $this->license->getKey(), [
                'product_reference_id' => $this->product->reference_id,
                'customer_id' => 'UPDATED-CUST',
            ]),
            $this->license->license_code
        );
    }

    public function test_creating_customer_via_api_creates_activity_log(): void
    {
        $this->assertNewLogContains(
            fn () => $this->postWithHeaders('/api/internal/customers', [
                'name' => 'Test Customer',
                'email' => 'test-' . uniqid() . '@example.com',
                'client_id' => 'CUST-' . uniqid(),
            ]),
            'Test Customer'
        );
    }

    public function test_updating_customer_via_api_creates_activity_log(): void
    {
        $customer = Customer::query()->create([
            'name' => 'Original Name',
            'email' => 'cust-' . uniqid() . '@example.com',
            'client_id' => 'CUST-' . uniqid(),
            'password' => bcrypt('password'),
        ]);

        $this->assertNewLogContains(
            fn () => $this->putWithHeaders('/api/internal/customers/' . $customer->getKey(), [
                'name' => 'Updated Customer',
            ]),
            'Updated Customer'
        );
    }

    public function test_publishing_product_via_api_creates_activity_log(): void
    {
        $this->product->update(['is_active' => false]);

        $this->assertNewLogContains(
            fn () => $this->postWithHeaders('/api/internal/activated-products/' . $this->product->getKey()),
            $this->product->name
        );
    }

    public function test_unpublishing_product_via_api_creates_activity_log(): void
    {
        $this->assertNewLogContains(
            fn () => $this->deleteWithHeaders('/api/internal/activated-products/' . $this->product->getKey()),
            $this->product->name
        );
    }

    public function test_activating_activation_via_api_creates_activity_log(): void
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
            'is_active' => false,
        ]);

        $this->assertNewLogContains(
            fn () => $this->postWithHeaders('/api/internal/activated-product-activations/' . $activation->getKey()),
            $this->license->license_code
        );
    }

    public function test_deactivating_activation_via_api_creates_activity_log(): void
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

        $this->assertNewLogContains(
            fn () => $this->deleteWithHeaders('/api/internal/activated-product-activations/' . $activation->getKey()),
            $this->license->license_code
        );
    }
}
