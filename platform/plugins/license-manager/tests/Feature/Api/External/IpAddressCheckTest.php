<?php

namespace Botble\LicenseManager\Tests\Feature\Api\External;

class IpAddressCheckTest extends ExternalApiTestCase
{
    public function test_accepts_valid_ipv4_address(): void
    {
        $response = $this->getWithHeaders('/api/external/connection-check', [
            'X-API-IP' => '192.168.1.1',
        ]);

        $response->assertStatus(200);
    }

    public function test_accepts_valid_ipv6_address(): void
    {
        $response = $this->getWithHeaders('/api/external/connection-check', [
            'X-API-IP' => '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
        ]);

        $response->assertStatus(200);
    }

    public function test_accepts_loopback_address(): void
    {
        $response = $this->getWithHeaders('/api/external/connection-check', [
            'X-API-IP' => '127.0.0.1',
        ]);

        $response->assertStatus(200);
    }

    public function test_rejects_invalid_ip_address(): void
    {
        $response = $this->getWithHeaders('/api/external/connection-check', [
            'X-API-IP' => 'not-an-ip',
        ]);

        $response->assertStatus(403);
    }

    public function test_rejects_missing_ip_header(): void
    {
        $headers = $this->externalHeaders();
        unset($headers['X-API-IP']);

        $response = $this->withHeaders($headers)->get('/api/external/connection-check');

        $response->assertStatus(400);
    }

    public function test_rejects_blacklisted_ip(): void
    {
        setting()->set([
            'lm_blacklisted_ips' => json_encode([['value' => '10.0.0.1']]),
        ])->save();

        $response = $this->getWithHeaders('/api/external/connection-check', [
            'X-API-IP' => '10.0.0.1',
        ]);

        $response->assertStatus(403);
    }

    public function test_accepts_non_blacklisted_ip(): void
    {
        setting()->set([
            'lm_blacklisted_ips' => json_encode([['value' => '10.0.0.1']]),
        ])->save();

        $response = $this->getWithHeaders('/api/external/connection-check', [
            'X-API-IP' => '10.0.0.2',
        ]);

        $response->assertStatus(200);
    }

    public function test_accepts_legacy_lb_ip_header(): void
    {
        $headers = $this->externalHeaders();
        unset($headers['X-API-IP']);
        $headers['LB-IP'] = '192.168.1.1';

        $response = $this->withHeaders($headers)->get('/api/external/connection-check');

        $response->assertStatus(200);
    }
}
