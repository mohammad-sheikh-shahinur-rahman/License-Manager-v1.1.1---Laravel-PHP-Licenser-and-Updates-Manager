<?php

namespace Botble\LicenseManager\Support;

class IpHelper
{
    public static function normalize(?string $ip): ?string
    {
        if ($ip === null || $ip === '') {
            return $ip;
        }

        $ip = trim($ip);

        $packed = @inet_pton($ip);

        if ($packed === false) {
            return $ip;
        }

        return inet_ntop($packed);
    }

    public static function extractIpv4FromMappedIpv6(?string $ip): ?string
    {
        if ($ip === null || $ip === '') {
            return null;
        }

        $ip = trim($ip);

        if (preg_match('/^::ffff:(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})$/i', $ip, $matches)) {
            return $matches[1];
        }

        $packed = @inet_pton($ip);

        if ($packed === false || strlen($packed) !== 16) {
            return null;
        }

        if (substr($packed, 0, 12) === "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff") {
            return inet_ntop(substr($packed, 12));
        }

        return null;
    }

    public static function isSameIp(?string $ip1, ?string $ip2): bool
    {
        if ($ip1 === null || $ip2 === null || $ip1 === '' || $ip2 === '') {
            return false;
        }

        $normalized1 = self::normalize($ip1);
        $normalized2 = self::normalize($ip2);

        if ($normalized1 === $normalized2) {
            return true;
        }

        $v4from1 = self::extractIpv4FromMappedIpv6($normalized1);
        $v4from2 = self::extractIpv4FromMappedIpv6($normalized2);

        if ($v4from1 !== null && $v4from1 === $normalized2) {
            return true;
        }

        if ($v4from2 !== null && $v4from2 === $normalized1) {
            return true;
        }

        if ($v4from1 !== null && $v4from2 !== null && $v4from1 === $v4from2) {
            return true;
        }

        return false;
    }
}
