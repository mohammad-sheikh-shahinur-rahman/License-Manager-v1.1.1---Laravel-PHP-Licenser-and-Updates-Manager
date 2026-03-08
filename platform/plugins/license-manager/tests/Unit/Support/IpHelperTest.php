<?php

namespace Botble\LicenseManager\Tests\Unit\Support;

use Botble\LicenseManager\Support\IpHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class IpHelperTest extends TestCase
{
    #[DataProvider('normalizeProvider')]
    public function test_normalize(?string $input, ?string $expected): void
    {
        $this->assertSame($expected, IpHelper::normalize($input));
    }

    public static function normalizeProvider(): array
    {
        return [
            'null' => [null, null],
            'empty string' => ['', ''],
            'ipv4 loopback' => ['127.0.0.1', '127.0.0.1'],
            'ipv4 standard' => ['192.168.1.1', '192.168.1.1'],
            'ipv6 loopback short' => ['::1', '::1'],
            'ipv6 loopback expanded' => ['0:0:0:0:0:0:0:1', '::1'],
            'ipv6 full zeros' => ['0000:0000:0000:0000:0000:0000:0000:0001', '::1'],
            'ipv6 mapped ipv4' => ['::ffff:192.168.1.1', '::ffff:192.168.1.1'],
            'ipv6 standard' => ['2001:0db8:0000:0000:0000:0000:0000:0001', '2001:db8::1'],
            'invalid ip' => ['not-an-ip', 'not-an-ip'],
            'whitespace trimmed' => [' 127.0.0.1 ', '127.0.0.1'],
        ];
    }

    #[DataProvider('extractIpv4Provider')]
    public function test_extract_ipv4_from_mapped_ipv6(?string $input, ?string $expected): void
    {
        $this->assertSame($expected, IpHelper::extractIpv4FromMappedIpv6($input));
    }

    public static function extractIpv4Provider(): array
    {
        return [
            'null' => [null, null],
            'empty string' => ['', null],
            'plain ipv4' => ['192.168.1.1', null],
            'mapped ipv4 lowercase' => ['::ffff:192.168.1.1', '192.168.1.1'],
            'mapped ipv4 uppercase' => ['::FFFF:10.0.0.1', '10.0.0.1'],
            'mapped loopback' => ['::ffff:127.0.0.1', '127.0.0.1'],
            'pure ipv6' => ['2001:db8::1', null],
            'ipv6 loopback' => ['::1', null],
        ];
    }

    #[DataProvider('isSameIpProvider')]
    public function test_is_same_ip(?string $ip1, ?string $ip2, bool $expected): void
    {
        $this->assertSame($expected, IpHelper::isSameIp($ip1, $ip2));
    }

    public static function isSameIpProvider(): array
    {
        return [
            'both null' => [null, null, false],
            'first null' => [null, '127.0.0.1', false],
            'second null' => ['127.0.0.1', null, false],
            'both empty' => ['', '', false],
            'identical ipv4' => ['127.0.0.1', '127.0.0.1', true],
            'different ipv4' => ['127.0.0.1', '192.168.1.1', false],
            'ipv6 loopback formats' => ['::1', '0:0:0:0:0:0:0:1', true],
            'ipv6 expanded vs short' => ['2001:0db8:0000:0000:0000:0000:0000:0001', '2001:db8::1', true],
            'mapped ipv6 vs ipv4' => ['::ffff:192.168.1.1', '192.168.1.1', true],
            'mapped ipv6 vs ipv4 loopback' => ['::ffff:127.0.0.1', '127.0.0.1', true],
            'different ips ipv4 vs ipv6' => ['192.168.1.1', '::1', false],
            'both mapped same' => ['::ffff:10.0.0.1', '::ffff:10.0.0.1', true],
            'both mapped different' => ['::ffff:10.0.0.1', '::ffff:10.0.0.2', false],
        ];
    }
}
