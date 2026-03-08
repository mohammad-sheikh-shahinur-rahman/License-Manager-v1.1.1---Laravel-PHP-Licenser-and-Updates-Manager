<?php

namespace Botble\LicenseManager\Support;

use Illuminate\Support\Facades\View;

class Helper
{
    public static function envatoPortfolioUrl(): string
    {
        return sprintf(
            'https://%s/user/%s/portfolio',
            setting('lm_envato_marketplace', 'codecanyon.net'),
            setting('lm_envato_owner_username', 'botble'),
        );
    }

    public static function getSupportedFileTypes(): array
    {
        return [
            'sql',
            'zip',
            'gz',
            'tar.gz',
            'tar.bz2',
            'tar.xz',
        ];
    }

    public static function viewPath(string $view): string
    {
        $themeName = setting('theme') ?: 'license';
        $themeView = "theme.{$themeName}::views.{$view}";

        if (View::exists($themeView)) {
            return $themeView;
        }

        return "plugins/license-manager::{$view}";
    }
}
