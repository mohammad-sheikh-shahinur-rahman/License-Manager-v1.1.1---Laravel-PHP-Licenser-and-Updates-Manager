<?php

namespace Botble\LicenseManager\Providers;

use Botble\Base\Facades\EmailHandler;
use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\LicenseManager\Events\LicenseActivated;
use Botble\LicenseManager\Events\LicenseDeactivated;
use Botble\LicenseManager\Events\LicenseVerified;
use Botble\LicenseManager\Events\UpdateDownloaded;
use Botble\LicenseManager\Http\Middleware\PublicVerifyEnabled;
use Botble\LicenseManager\LicenseManager;
use Botble\LicenseManager\Listeners\LogAdminLogin;
use Botble\LicenseManager\Listeners\LogCustomerActivity;
use Botble\LicenseManager\Models\Customer;
use Botble\LicenseManager\Support\Helper;
use Illuminate\Auth\Events\Login;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;

class LicenseManagerServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        $this
            ->setNamespace('plugins/license-manager')
            ->loadAndPublishConfigurations(['permissions', 'email']);

        $this->app->singleton(LicenseManager::class);

        AliasLoader::getInstance()
            ->alias('LicenseManagerHelper', Helper::class);

        $this->app->register(ConsoleServiceProvider::class);

        $this->app->register(HookServiceProvider::class);
    }

    public function boot(): void
    {
        $this
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadRoutes(['web', 'api'])
            ->loadMigrations()
            ->publishAssets();

        $this->configRateLimiterForApi();

        $this->configCustomerAuthenticationGuard();

        $this->registerMiddleware();

        EmailHandler::addTemplateSettings('license-manager', config('plugins.license-manager.email', []));

        Event::listen(Login::class, LogAdminLogin::class);

        $customerActivityListener = LogCustomerActivity::class;
        Event::listen(LicenseActivated::class, [$customerActivityListener, 'handleLicenseActivated']);
        Event::listen(LicenseDeactivated::class, [$customerActivityListener, 'handleLicenseDeactivated']);
        Event::listen(LicenseVerified::class, [$customerActivityListener, 'handleLicenseVerified']);
        Event::listen(UpdateDownloaded::class, [$customerActivityListener, 'handleUpdateDownloaded']);
    }

    protected function registerMiddleware(): void
    {
        $this->app[Router::class]->aliasMiddleware('lm.public.verify.enabled', PublicVerifyEnabled::class);
    }

    public function configRateLimiterForApi(): void
    {
        RateLimiter::for('license-manager', function (Request $request) {
            $limit = (int) setting('lm_requests_rate_limiting_period', 3000);
            $method = setting('lm_requests_rate_limiting_method', 'ip_address');

            $key = match ($method) {
                'api_key' => $request->header('X-API-KEY') ?: $request->header('LB-API-KEY') ?: $request->ip(),
                'api_key_ip_address' => ($request->header('X-API-KEY') ?: $request->header('LB-API-KEY') ?: '') . '|' . $request->ip(),
                default => $request->ip(),
            };

            return Limit::perMinute($limit)->by($key);
        });
    }

    public function configCustomerAuthenticationGuard(): void
    {
        $this->app['config']->set([
            'auth.guards.lm_customer' => [
                'driver' => 'session',
                'provider' => 'lm_customers',
            ],
            'auth.providers.lm_customers' => [
                'driver' => 'eloquent',
                'model' => Customer::class,
            ],
            'auth.passwords.lm_customers' => [
                'provider' => 'lm_customers',
                'table' => 'lm_customer_password_reset_tokens',
                'expire' => 60,
            ],
        ]);
    }
}
