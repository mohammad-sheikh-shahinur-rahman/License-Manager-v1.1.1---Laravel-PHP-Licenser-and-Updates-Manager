<?php

namespace Botble\LicenseManager\Tests\Unit\Services;

use Botble\LicenseManager\Services\CronService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CronServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CronService $cronService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cronService = app(CronService::class);
    }

    public function test_process_license_expirations_returns_array(): void
    {
        $result = $this->cronService->processLicenseExpirations();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('warnings_sent', $result);
        $this->assertArrayHasKey('expired_logged', $result);
        $this->assertArrayHasKey('updates_expired', $result);
    }

    public function test_process_license_expirations_skips_when_disabled(): void
    {
        setting()->set(['lm_send_expiration_warnings' => '0'])->save();

        $result = $this->cronService->processLicenseExpirations();

        $this->assertEquals(0, $result['warnings_sent']);
    }

    public function test_process_auto_blacklist_returns_array(): void
    {
        $result = $this->cronService->processAutoBlacklist();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('domains_blacklisted', $result);
        $this->assertArrayHasKey('ips_blacklisted', $result);
    }

    public function test_process_auto_blacklist_skips_when_threshold_zero(): void
    {
        setting()->set([
            'lm_blacklist_domain_after_failed_attempts' => 0,
            'lm_blacklist_ip_after_failed_attempts' => 0,
        ])->save();

        $result = $this->cronService->processAutoBlacklist();

        $this->assertEquals(0, $result['domains_blacklisted']);
        $this->assertEquals(0, $result['ips_blacklisted']);
    }

    public function test_process_auto_blacklist_returns_zero_with_no_failures(): void
    {
        setting()->set([
            'lm_blacklist_domain_after_failed_attempts' => 1,
            'lm_blacklist_ip_after_failed_attempts' => 1,
        ])->save();

        $result = $this->cronService->processAutoBlacklist();

        $this->assertEquals(0, $result['domains_blacklisted']);
        $this->assertEquals(0, $result['ips_blacklisted']);
    }
}
