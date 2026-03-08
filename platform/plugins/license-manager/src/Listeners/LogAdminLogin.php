<?php

namespace Botble\LicenseManager\Listeners;

use Botble\LicenseManager\Models\ActivityLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Request;
use Throwable;

class LogAdminLogin
{
    public function handle(Login $event): void
    {
        if ($event->guard !== 'web') {
            return;
        }

        $ip = Request::ip();
        $userAgent = Request::userAgent() ?: '';
        $browser = $this->parseBrowser($userAgent);
        $platform = $this->parsePlatform($userAgent);
        $location = $this->getLocation($ip);

        $message = trans('plugins/license-manager::license-manager.activity_log.admin_login', [
            'ip' => e($ip),
            'location' => $location ? e($location) . ' ' : '',
            'browser' => e($browser),
            'platform' => e($platform),
        ]);

        ActivityLog::log($message);
    }

    protected function parseBrowser(string $userAgent): string
    {
        $browsers = [
            'OPR' => 'Opera',
            'Edg' => 'Edge',
            'Chrome' => 'Chrome',
            'Firefox' => 'Firefox',
            'Safari' => 'Safari',
        ];

        foreach ($browsers as $key => $name) {
            if (preg_match("/{$key}[\/ ]([\d.]+)/", $userAgent, $matches)) {
                return $name . ' ' . $matches[1];
            }
        }

        return trans('plugins/license-manager::license-manager.activity_log.unknown_browser');
    }

    protected function parsePlatform(string $userAgent): string
    {
        $platforms = [
            '/Macintosh|Mac OS X/' => 'Mac OS X',
            '/Windows NT 10/' => 'Windows 10',
            '/Windows NT 6\.3/' => 'Windows 8.1',
            '/Windows NT 6\.2/' => 'Windows 8',
            '/Windows NT 6\.1/' => 'Windows 7',
            '/Windows/' => 'Windows',
            '/Linux/' => 'Linux',
            '/Android/' => 'Android',
            '/iPhone|iPad/' => 'iOS',
        ];

        foreach ($platforms as $pattern => $name) {
            if (preg_match($pattern, $userAgent)) {
                return $name;
            }
        }

        return trans('plugins/license-manager::license-manager.activity_log.unknown_os');
    }

    protected function getLocation(string $ip): string
    {
        if (in_array($ip, ['127.0.0.1', '::1'])) {
            return '';
        }

        try {
            $response = Http::timeout(3)->get("http://ip-api.com/json/{$ip}", [
                'fields' => 'city,country,status',
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (($data['status'] ?? '') === 'success' && ! empty($data['city'])) {
                    return sprintf('(%s, %s)', $data['city'], $data['country']);
                }
            }
        } catch (Throwable) {
        }

        return '';
    }
}
