<?php

namespace Botble\LegacyApi\Providers;

use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;

class LegacyApiServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function boot(): void
    {
        if (! is_plugin_active('license-manager')) {
            return;
        }

        $this->app->register(HookServiceProvider::class);

        $this
            ->setNamespace('plugins/licensebox-legacy-api')
            ->loadRoutes(['api'])
            ->loadMigrations();
    }
}
