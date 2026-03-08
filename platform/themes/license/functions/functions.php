<?php

use Botble\Theme\Supports\ThemeSupport;

app()->booted(function (): void {
    ThemeSupport::registerSocialLinks();
    ThemeSupport::registerSiteCopyright();
    ThemeSupport::registerSiteLogoHeight();

    app('events')->listen('core.page::registering-templates', function (): void {
        register_page_template([
            'main' => __('Dashboard'),
        ]);
    });
});
