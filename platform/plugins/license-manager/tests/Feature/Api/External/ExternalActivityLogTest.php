<?php

namespace Botble\LicenseManager\Tests\Feature\Api\External;

use Botble\LicenseManager\Models\ActivityLog;
use Botble\LicenseManager\Models\CustomerActivityLog;

class ExternalActivityLogTest extends ExternalApiTestCase
{
    public function test_license_activation_creates_admin_activity_log(): void
    {
        $response = $this->postWithHeaders('/api/external/license/activate', [
            'verify_type' => 'non_envato',
            'product_id' => $this->product->reference_id,
            'license_code' => $this->license->license_code,
            'client_name' => $this->license->customer_id,
        ]);

        $response->assertStatus(200)->assertJson(['status' => true]);

        $log = ActivityLog::query()->latest('id')->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString($this->license->license_code, $log->message);
        $this->assertStringContainsString($this->product->name, $log->message);
    }

    public function test_license_activation_creates_customer_activity_log(): void
    {
        $response = $this->postWithHeaders('/api/external/license/activate', [
            'verify_type' => 'non_envato',
            'product_id' => $this->product->reference_id,
            'license_code' => $this->license->license_code,
            'client_name' => $this->license->customer_id,
        ]);

        $response->assertStatus(200)->assertJson(['status' => true]);

        $log = CustomerActivityLog::query()
            ->where('customer_id', $this->license->customer_id)
            ->where('type', 'license_activated')
            ->latest('id')
            ->first();

        $this->assertNotNull($log, 'Customer activity log should be created on license activation');
        $this->assertStringContainsString($this->license->license_code, $log->message);
    }

    public function test_license_deactivation_creates_admin_activity_log(): void
    {
        // First activate
        $activateResponse = $this->postWithHeaders('/api/external/license/activate', [
            'verify_type' => 'non_envato',
            'product_id' => $this->product->reference_id,
            'license_code' => $this->license->license_code,
            'client_name' => $this->license->customer_id,
        ]);
        $licenseData = $activateResponse->json('lic_response');

        // Then deactivate
        $response = $this->postWithHeaders('/api/external/license/deactivate', [
            'product_id' => $this->product->reference_id,
            'license_data' => $licenseData,
            'client_name' => $this->license->customer_id,
        ]);

        $response->assertStatus(200)->assertJson(['status' => true]);

        $log = ActivityLog::query()->latest('id')->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString($this->license->license_code, $log->message);
    }

    public function test_license_deactivation_creates_customer_activity_log(): void
    {
        // First activate
        $activateResponse = $this->postWithHeaders('/api/external/license/activate', [
            'verify_type' => 'non_envato',
            'product_id' => $this->product->reference_id,
            'license_code' => $this->license->license_code,
            'client_name' => $this->license->customer_id,
        ]);
        $licenseData = $activateResponse->json('lic_response');

        // Then deactivate
        $this->postWithHeaders('/api/external/license/deactivate', [
            'product_id' => $this->product->reference_id,
            'license_data' => $licenseData,
            'client_name' => $this->license->customer_id,
        ]);

        $log = CustomerActivityLog::query()
            ->where('customer_id', $this->license->customer_id)
            ->where('type', 'license_deactivated')
            ->latest('id')
            ->first();

        $this->assertNotNull($log, 'Customer activity log should be created on license deactivation');
    }

    public function test_license_verification_creates_customer_activity_log(): void
    {
        // First activate to get license data
        $activateResponse = $this->postWithHeaders('/api/external/license/activate', [
            'verify_type' => 'non_envato',
            'product_id' => $this->product->reference_id,
            'license_code' => $this->license->license_code,
            'client_name' => $this->license->customer_id,
        ]);
        $licenseData = $activateResponse->json('lic_response');

        // Then verify
        $this->postWithHeaders('/api/external/license/verify', [
            'product_id' => $this->product->reference_id,
            'license_data' => $licenseData,
            'client_name' => $this->license->customer_id,
        ]);

        $log = CustomerActivityLog::query()
            ->where('customer_id', $this->license->customer_id)
            ->where('type', 'license_verified')
            ->latest('id')
            ->first();

        $this->assertNotNull($log, 'Customer activity log should be created on license verification');
    }

    public function test_activity_log_messages_use_translation_keys(): void
    {
        $this->postWithHeaders('/api/external/license/activate', [
            'verify_type' => 'non_envato',
            'product_id' => $this->product->reference_id,
            'license_code' => $this->license->license_code,
            'client_name' => $this->license->customer_id,
        ]);

        $adminLog = ActivityLog::query()->latest('id')->first();
        $this->assertNotNull($adminLog);
        // Should NOT contain raw translation key
        $this->assertStringNotContainsString('activity_log.license_activated_via_api', $adminLog->message);
        // Should contain actual translated content
        $this->assertStringContainsString($this->license->license_code, $adminLog->message);
    }
}
