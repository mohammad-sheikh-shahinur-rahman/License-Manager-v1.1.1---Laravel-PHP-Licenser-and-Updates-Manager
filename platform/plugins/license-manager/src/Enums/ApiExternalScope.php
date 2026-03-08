<?php

namespace Botble\LicenseManager\Enums;

use Botble\LicenseManager\Enums\Concerns\HasChoices;

enum ApiExternalScope: string
{
    use HasChoices;

    case ConnectionCheck = 'connection:check';

    case LicenseActivate = 'license:activate';

    case LicenseDeactivate = 'license:deactivate';

    case LicenseVerify = 'license:verify';

    case UpdateList = 'update:list';

    case UpdateLatest = 'update:latest';

    case UpdateCheck = 'update:check';

    case UpdateDownload = 'update:download';

    case LicenseCheck = 'license:check';

    public static function getScopeFromPath(string $path): ?self
    {
        // Handle parameterized paths (e.g., update/{version}/download/{type})
        if (preg_match('#^update/[^/]+/download/[^/]+(/size)?$#', $path)) {
            return self::UpdateDownload;
        }

        // Handle legacy parameterized paths (e.g., download_update/{version} or download_update/{type}/{version})
        if (preg_match('#^download_update/[^/]+(/[^/]+)?$#', $path)) {
            return self::UpdateDownload;
        }

        // Handle legacy parameterized paths (e.g., get_update_size/{version} or get_update_size/{type}/{version})
        if (preg_match('#^get_update_size/[^/]+(/[^/]+)?$#', $path)) {
            return self::UpdateDownload;
        }

        return match ($path) {
            // Modern paths
            'connection-check' => self::ConnectionCheck,
            'license/activate' => self::LicenseActivate,
            'license/deactivate' => self::LicenseDeactivate,
            'license/verify' => self::LicenseVerify,
            'update/list' => self::UpdateList,
            'update/latest' => self::UpdateLatest,
            'update/check' => self::UpdateCheck,
            'license/check' => self::LicenseCheck,
            // Legacy paths
            'check_connection_ext' => self::ConnectionCheck,
            'activate_license' => self::LicenseActivate,
            'deactivate_license' => self::LicenseDeactivate,
            'verify_license' => self::LicenseVerify,
            'check_update' => self::UpdateCheck,
            'latest_version' => self::UpdateLatest,
            'download_update' => self::UpdateDownload,
            'get_update_size' => self::UpdateDownload,
            default => null,
        };
    }
}
