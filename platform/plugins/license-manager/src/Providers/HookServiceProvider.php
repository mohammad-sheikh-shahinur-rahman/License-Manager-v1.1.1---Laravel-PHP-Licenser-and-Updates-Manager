<?php

namespace Botble\LicenseManager\Providers;

use Botble\Base\Facades\Assets;
use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Facades\PanelSectionManager;
use Botble\Base\Forms\FormAbstract;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\PanelSections\PanelSectionItem;
use Botble\Base\Rules\OnOffRule;
use Botble\Captcha\Events\CaptchaRendering;
use Botble\Captcha\Forms\CaptchaSettingForm;
use Botble\Dashboard\Events\RenderingDashboardWidgets;
use Botble\Dashboard\Supports\DashboardWidgetInstance;
use Botble\DataSynchronize\PanelSections\ExportPanelSection;
use Botble\DataSynchronize\PanelSections\ImportPanelSection;
use Botble\LicenseManager\Http\Middleware\RedirectToSettingIfCurrentPageIsSystem;
use Botble\LicenseManager\Models\Customer;
use Botble\LicenseManager\Models\Product;
use Botble\LicenseManager\Models\ProductActivation;
use Botble\LicenseManager\Models\ProductLicense;
use Botble\LicenseManager\PanelSections\LicenseManagerPanelSection;
use Botble\LicenseManager\Support\Helper;
use Botble\SocialLogin\Facades\SocialService;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $events = $this->app['events'];

        $events->listen(RenderingDashboardWidgets::class, [$this, 'registerDashboardWidgets']);
        $events->listen(RouteMatched::class, [$this, 'registerDashboardMenus']);

        DashboardMenu::default()->beforeRetrieving(function (): void {
            DashboardMenu::make()
                ->registerItem([
                    'id' => 'cms-core-page',
                    'priority' => 51,
                    'name' => trans('packages/page::pages.menu_name'),
                    'icon' => 'ti ti-notebook',
                    'url' => fn () => route('pages.index'),
                    'permissions' => ['pages.index'],
                ]);
        });
        DashboardMenu::for('license-manager-customer')->beforeRetrieving(function (): void {
            DashboardMenu::make()
                ->registerItem([
                    'id' => 'cms-license-manager-customer-dashboard',
                    'priority' => 1,
                    'parent_id' => null,
                    'name' => trans('plugins/license-manager::customer.dashboard'),
                    'url' => fn () => route('lm.customer.dashboard'),
                    'icon' => 'ti ti-home',
                ])
                ->when(setting('lm_customer_page_licenses', true), function (): void {
                    DashboardMenu::make()
                        ->registerItem([
                            'id' => 'cms-license-manager-customer-product-licenses',
                            'priority' => 10,
                            'parent_id' => null,
                            'name' => trans('plugins/license-manager::license-manager.customers.product_licenses'),
                            'url' => fn () => route('lm.customer.product-licenses.index'),
                            'icon' => 'ti ti-circle-key',
                        ]);
                })
                ->when(setting('lm_customer_page_activations', true), function (): void {
                    DashboardMenu::make()
                        ->registerItem([
                            'id' => 'cms-license-manager-customer-product-activations',
                            'priority' => 20,
                            'parent_id' => null,
                            'name' => trans('plugins/license-manager::license-manager.customers.product_activations'),
                            'url' => fn () => route('lm.customer.product-activations.index'),
                            'icon' => 'ti ti-clock-check',
                        ]);
                })
                ->registerItem([
                    'id' => 'cms-license-manager-customer-settings',
                    'priority' => 100,
                    'parent_id' => null,
                    'name' => trans('plugins/license-manager::customer.settings'),
                    'url' => fn () => route('lm.customer.settings.index'),
                    'icon' => 'ti ti-settings',
                ]);
        });
        $this->registerDataSynchronizePanelSections();

        $events->listen(RouteMatched::class, [$this, 'registerSettingPanelSections']);
        $events->listen(RouteMatched::class, [$this, 'registerMiddlewareRedirectSettingsWhenAccessSystemPage']);
        $events->listen(RouteMatched::class, [$this, 'setupCaptchaWhenAvailable']);
        $events->listen(RouteMatched::class, [$this, 'registerSocialLoginModule']);

        if (class_exists(CaptchaRendering::class)) {
            $events->listen(CaptchaRendering::class, [$this, 'registerCaptchaRenderingListener']);
        }
    }

    public function registerSocialLoginModule(): void
    {
        static $registered = false;

        if ($registered || ! is_plugin_active('social-login')) {
            return;
        }

        $registered = true;

        add_filter('social_login_callback_url', function (string $url, string $provider): string {
            return route('lm.customer.auth.envato.legacy-callback');
        }, 20, 2);

        SocialService::registerModule([
            'guard' => 'lm_customer',
            'model' => Customer::class,
            'login_url' => fn () => route('lm.customer.auth.login'),
            'redirect_url' => fn () => route('lm.customer.dashboard'),
            'use_css' => true,
            'view' => Helper::viewPath('customer.auth.partials.social-login-options'),
        ]);

        add_filter('social_login_before_creating_account', function ($result, $oAuth, array $providerData) {
            if (Arr::get($providerData, 'model') !== Customer::class) {
                return $result;
            }

            $clientId = $oAuth->getNickname() ?: $oAuth->getId();

            $existingCustomer = Customer::query()->where('client_id', $clientId)->first();

            if ($existingCustomer) {
                Auth::guard('lm_customer')->login($existingCustomer, true);

                $redirectUrl = route('lm.customer.dashboard');

                if (session()->has('url.intended')) {
                    $redirectUrl = session('url.intended');
                }

                return BaseHttpResponse::make()
                    ->setNextUrl($redirectUrl)
                    ->setMessage(trans('core/acl::auth.login.success'));
            }

            return $result;
        }, 20, 3);

        add_filter('social_login_before_saving_account', function (array $data, $oAuth, array $providerData): array {
            if (Arr::get($providerData, 'model') !== Customer::class) {
                return $data;
            }

            $data['client_id'] = $oAuth->getNickname() ?: $oAuth->getId();

            return $data;
        }, 20, 3);
    }

    protected function registerDataSynchronizePanelSections(): void
    {
        PanelSectionManager::setGroupId('data-synchronize')->beforeRendering(function (): void {
            PanelSectionManager::default()
                ->registerItem(
                    ExportPanelSection::class,
                    fn () => PanelSectionItem::make('licenses')
                        ->setTitle(trans('plugins/license-manager::license-manager.export.name'))
                        ->withDescription(trans('plugins/license-manager::license-manager.export.description'))
                        ->withPriority(999)
                        ->withPermission('lm.licenses.export')
                        ->withRoute('tools.data-synchronize.export.licenses.index')
                )
                ->registerItem(
                    ImportPanelSection::class,
                    fn () => PanelSectionItem::make('licenses')
                        ->setTitle(trans('plugins/license-manager::license-manager.import.name'))
                        ->withDescription(trans('plugins/license-manager::license-manager.import.description'))
                        ->withPriority(999)
                        ->withPermission('lm.licenses.import')
                        ->withRoute('tools.data-synchronize.import.licenses.index')
                );
        });
    }

    public function registerDashboardMenus(): void
    {
        DashboardMenu::make()
            ->registerItem([
                'id' => 'ls-licenses',
                'priority' => 50,
                'parent_id' => null,
                'name' => trans('plugins/license-manager::license-manager.title'),
                'icon' => 'ti ti-certificate',
                'permissions' => ['lm.licenses.index'],
            ])
            ->registerItem([
                'id' => 'ls-products',
                'priority' => 10,
                'parent_id' => 'ls-licenses',
                'name' => trans('plugins/license-manager::license-manager.products.title'),
                'icon' => 'ti ti-box',
                'url' => fn () => route('lm.products.index'),
                'permissions' => ['lm.products.index'],
            ])
            ->registerItem([
                'id' => 'ls-licenses-all',
                'priority' => 20,
                'parent_id' => 'ls-licenses',
                'name' => trans('plugins/license-manager::license-manager.licenses.title'),
                'icon' => 'ti ti-certificate',
                'url' => fn () => route('lm.licenses.index'),
                'permissions' => ['lm.licenses.index'],
            ])
            ->registerItem([
                'id' => 'ls-bulk-generate',
                'priority' => 25,
                'parent_id' => 'ls-licenses',
                'name' => trans('plugins/license-manager::license-manager.bulk_generate.title'),
                'icon' => 'ti ti-stack-2',
                'url' => fn () => route('lm.bulk-generate.index'),
                'permissions' => ['lm.licenses.create'],
            ])
            ->registerItem([
                'id' => 'ls-activations',
                'priority' => 30,
                'parent_id' => 'ls-licenses',
                'name' => trans('plugins/license-manager::license-manager.activations.title'),
                'icon' => 'ti ti-article',
                'url' => fn () => route('lm.activations.index'),
                'permissions' => ['lm.activations.index'],
            ])
            ->registerItem([
                'id' => 'ls-customers',
                'priority' => 40,
                'parent_id' => 'ls-licenses',
                'name' => trans('plugins/license-manager::license-manager.customers.title'),
                'icon' => 'ti ti-users',
                'url' => fn () => route('lm.customers.index'),
                'permissions' => ['lm.customers.index'],
            ])
            ->registerItem([
                'id' => 'ls-update-downloads',
                'priority' => 50,
                'parent_id' => 'ls-licenses',
                'name' => trans('plugins/license-manager::license-manager.update_downloads.title'),
                'icon' => 'ti ti-cloud-download',
                'url' => fn () => route('lm.update-downloads.index'),
                'permissions' => ['lm.update-downloads.index'],
            ])
            ->registerItem([
                'id' => 'ls-activity-logs',
                'priority' => 60,
                'parent_id' => 'ls-licenses',
                'name' => trans('plugins/license-manager::license-manager.activity_log.title'),
                'icon' => 'ti ti-history',
                'url' => fn () => route('lm.activity-logs.index'),
                'permissions' => ['lm.activity-logs.index'],
            ])
            ->registerItem([
                'id' => 'ls-tools-generate-helper',
                'priority' => 70,
                'parent_id' => 'ls-licenses',
                'name' => trans('plugins/license-manager::license-manager.tools.generate_helper'),
                'icon' => 'ti ti-code',
                'url' => fn () => route('lm.generate-helper'),
                'permissions' => ['lm.generate-helper'],
            ])
            ->registerItem([
                'id' => 'ls-tools-manual-cron',
                'priority' => 80,
                'parent_id' => 'ls-licenses',
                'name' => trans('plugins/license-manager::license-manager.tools.run_manual_cron'),
                'icon' => 'ti ti-clock-play',
                'url' => fn () => route('lm.manual-cron'),
                'permissions' => ['lm.manual-cron'],
            ])
            ->registerItem([
                'id' => 'ls-tools-php-obfuscator',
                'priority' => 90,
                'parent_id' => 'ls-licenses',
                'name' => trans('plugins/license-manager::license-manager.php_obfuscator.title'),
                'icon' => 'ti ti-lock-code',
                'url' => fn () => route('lm.php-obfuscator'),
                'permissions' => ['lm.php-obfuscator'],
            ])
            ->removeItem('cms-core-system')
            ->removeItem('cms-core-tools');
    }

    public function registerSettingPanelSections(): void
    {
        PanelSectionManager::default()
            ->register(LicenseManagerPanelSection::class)
            ->moveGroup('system', 'settings');
    }

    public function registerMiddlewareRedirectSettingsWhenAccessSystemPage(): void
    {
        $this->app[Router::class]->pushMiddlewareToGroup('web', RedirectToSettingIfCurrentPageIsSystem::class);
    }

    public function registerCaptchaRenderingListener(CaptchaRendering $event): void
    {
        add_filter(BASE_FILTER_HEAD_LAYOUT_TEMPLATE, function ($html) use ($event) {
            return $html . $event->head;
        }, 120);

        add_filter(BASE_FILTER_FOOTER_LAYOUT_TEMPLATE, function ($html) use ($event) {
            return $html . $event->footer;
        }, 120);
    }

    public function setupCaptchaWhenAvailable(): void
    {
        if (! is_plugin_active('captcha')) {
            return;
        }

        add_filter(BASE_FILTER_BEFORE_RENDER_FORM, [$this, 'addCustomerCaptchaSettingForm'], 9999);

        add_filter('captcha_settings_validation_rules', [$this, 'addCustomerCaptchaSettingRules'], 99);
    }

    public function addCustomerCaptchaSettingForm(FormAbstract $form): FormAbstract
    {
        if ($form instanceof CaptchaSettingForm) {
            $form
                ->addAfter('close_fieldset_captcha_setting', 'lm_customer_math_captcha_enabled', 'onOffCheckbox', [
                    'label' => trans(
                        'plugins/license-manager::license-manager.captcha.enable_math_captcha_in_the_customer_authentication_page'
                    ),
                    'value' => setting('lm_customer_math_captcha_enabled', false),
                ])
                ->addAfter('close_fieldset_captcha_setting', 'lm_customer_recaptcha_enabled', 'onOffCheckbox', [
                    'label' => trans(
                        'plugins/license-manager::license-manager.captcha.enable_recaptcha_in_the_customer_authentication_page'
                    ),
                    'value' => setting('lm_customer_recaptcha_enabled', false),
                ]);
        }

        return $form;
    }

    public function addCustomerCaptchaSettingRules(array $rules): array
    {
        return array_merge($rules, [
            'lm_customer_recaptcha_enabled' => $onOffRule = new OnOffRule(),
            'lm_customer_math_captcha_enabled' => $onOffRule,
        ]);
    }

    public function registerDashboardWidgets(): void
    {
        add_action(DASHBOARD_ACTION_REGISTER_SCRIPTS, [$this, 'registerDashboardScripts'], 18);

        add_filter(DASHBOARD_FILTER_ADMIN_LIST, function (array $widgets, Collection $widgetSettings) {
            return (new DashboardWidgetInstance())
                ->setType('stats')
                ->setPermission('lm.products.index')
                ->setTitle(trans('plugins/license-manager::license-manager.products.title'))
                ->setKey('widget-total-products')
                ->setIcon('ti ti-box')
                ->setColor('info')
                ->setStatsTotal(Product::query()->count())
                ->setRoute(route('lm.products.index'))
                ->setColumn('col-12 col-md-6 col-lg-4')
                ->setPriority(800)
                ->init($widgets, $widgetSettings);
        }, 800, 2);

        add_filter(DASHBOARD_FILTER_ADMIN_LIST, function (array $widgets, Collection $widgetSettings) {
            return (new DashboardWidgetInstance())
                ->setType('stats')
                ->setPermission('lm.customers.index')
                ->setTitle(trans('plugins/license-manager::license-manager.customers.title'))
                ->setKey('widget-total-customers')
                ->setIcon('ti ti-users')
                ->setColor('info')
                ->setStatsTotal(Customer::query()->count())
                ->setRoute(route('lm.customers.index'))
                ->setColumn('col-12 col-md-6 col-lg-4')
                ->setPriority(810)
                ->init($widgets, $widgetSettings);
        }, 810, 2);

        add_filter(DASHBOARD_FILTER_ADMIN_LIST, function (array $widgets, Collection $widgetSettings) {
            return (new DashboardWidgetInstance())
                ->setType('stats')
                ->setPermission('lm.licenses.index')
                ->setTitle(trans('plugins/license-manager::license-manager.licenses.title'))
                ->setKey('widget-total-license')
                ->setIcon('ti ti-license')
                ->setColor('info')
                ->setStatsTotal(ProductLicense::query()->count())
                ->setRoute(route('lm.licenses.index'))
                ->setColumn('col-12 col-md-6 col-lg-4')
                ->setPriority(810)
                ->init($widgets, $widgetSettings);
        }, 810, 2);

        add_filter(DASHBOARD_FILTER_ADMIN_LIST, function (array $widgets, Collection $widgetSettings) {
            return (new DashboardWidgetInstance())
                ->setType('stats')
                ->setPermission('lm.activations.index')
                ->setTitle(trans('plugins/license-manager::license-manager.licenses.activations'))
                ->setKey('widget-total-activations')
                ->setIcon('ti ti-checklist')
                ->setColor('info')
                ->setStatsTotal(ProductActivation::query()->count())
                ->setRoute(route('lm.activations.index'))
                ->setColumn('col-12 col-md-6 col-lg-4')
                ->setPriority(820)
                ->init($widgets, $widgetSettings);
        }, 820, 2);

        add_filter(DASHBOARD_FILTER_ADMIN_LIST, function (array $widgets, Collection $widgetSettings) {
            return (new DashboardWidgetInstance())
                ->setType('stats')
                ->setPermission('packages.license-manager')
                ->setTitle(trans('plugins/license-manager::license-manager.update_downloads.title'))
                ->setKey('widget-total-update-downloads')
                ->setIcon('ti ti-cloud-down')
                ->setColor('info')
                ->setStatsTotal(DB::table('lm_update_downloads')->count())
                ->setRoute(route('lm.update-downloads.index'))
                ->setColumn('col-12 col-md-6 col-lg-4')
                ->setPriority(830)
                ->init($widgets, $widgetSettings);
        }, 930, 2);

        add_filter(DASHBOARD_FILTER_ADMIN_LIST, function (array $widgets, Collection $widgetSettings) {
            $apiCallsCount = 0;
            if (Schema::hasTable('api_logs')) {
                $apiCallsCount = (int) DB::table('api_logs')->sum('count');
            }

            return (new DashboardWidgetInstance())
                ->setType('stats')
                ->setPermission('packages.license-manager')
                ->setTitle(trans('plugins/license-manager::license-manager.dashboard_widgets.api_calls'))
                ->setKey('widget-total-api-calls')
                ->setIcon('ti ti-api')
                ->setColor('info')
                ->setStatsTotal($apiCallsCount)
                ->setRoute('')
                ->setColumn('col-12 col-md-6 col-lg-4')
                ->setPriority(840)
                ->init($widgets, $widgetSettings);
        }, 840, 2);

        add_filter(DASHBOARD_FILTER_ADMIN_LIST, function (array $widgets, Collection $widgetSettings) {
            return (new DashboardWidgetInstance())
                ->setType('widget')
                ->setPermission('lm.licenses.index')
                ->setTitle(trans('plugins/license-manager::license-manager.dashboard_widgets.licenses_share'))
                ->setKey('widget-licenses-share')
                ->setRoute(route('lm.widgets.licenses-share'))
                ->setColumn('col-12 col-md-6')
                ->setIsEqualHeight(false)
                ->setHasLoadCallback(true)
                ->init($widgets, $widgetSettings);
        }, 850, 2);

        add_filter(DASHBOARD_FILTER_ADMIN_LIST, function (array $widgets, Collection $widgetSettings) {
            return (new DashboardWidgetInstance())
                ->setType('widget')
                ->setPermission('lm.licenses.index')
                ->setTitle(trans('plugins/license-manager::license-manager.dashboard_widgets.licenses_activations_chart'))
                ->setKey('widget-licenses-activations-chart')
                ->setRoute(route('lm.widgets.licenses-activations-chart'))
                ->setColumn('col-12 col-md-6')
                ->setIsEqualHeight(false)
                ->setHasLoadCallback(true)
                ->init($widgets, $widgetSettings);
        }, 860, 2);

        add_filter(DASHBOARD_FILTER_ADMIN_LIST, function (array $widgets, Collection $widgetSettings) {
            return (new DashboardWidgetInstance())
                ->setType('widget')
                ->setPermission('lm.customers.index')
                ->setTitle(trans('plugins/license-manager::license-manager.dashboard_widgets.top_customers'))
                ->setKey('widget-top-customers')
                ->setRoute(route('lm.widgets.top-customers'))
                ->setColumn('col-12 col-md-6')
                ->setIsEqualHeight(false)
                ->setHasLoadCallback(true)
                ->init($widgets, $widgetSettings);
        }, 865, 2);

        add_filter(DASHBOARD_FILTER_ADMIN_LIST, function (array $widgets, Collection $widgetSettings) {
            return (new DashboardWidgetInstance())
                ->setType('widget')
                ->setPermission('lm.products.index')
                ->setTitle(trans('plugins/license-manager::license-manager.dashboard_widgets.top_products'))
                ->setKey('widget-top-products')
                ->setRoute(route('lm.widgets.top-products'))
                ->setColumn('col-12 col-md-6')
                ->setIsEqualHeight(false)
                ->setHasLoadCallback(true)
                ->init($widgets, $widgetSettings);
        }, 866, 2);

        add_filter(DASHBOARD_FILTER_ADMIN_LIST, function (array $widgets, Collection $widgetSettings) {
            return (new DashboardWidgetInstance())
                ->setType('widget')
                ->setPermission('lm.activations.index')
                ->setTitle(trans('plugins/license-manager::license-manager.dashboard_widgets.recent_activities'))
                ->setKey('widget-recent-activities')
                ->setRoute(route('lm.widgets.recent-activities'))
                ->setColumn('col-12')
                ->setIsEqualHeight(false)
                ->init($widgets, $widgetSettings);
        }, 870, 2);

        add_filter(DASHBOARD_FILTER_ADMIN_LIST, function (array $widgets) {
            Arr::forget($widgets, ['widget_total_users', 'widget_total_packages']);

            return $widgets;
        }, 9999);
    }

    public function registerDashboardScripts(): void
    {
        if (Auth::guard()->user()->hasAnyPermission(['lm.licenses.index'])) {
            Assets::addScripts(['raphael', 'morris'])
                ->addStyles(['morris'])
                ->addScriptsDirectly([
                    'vendor/core/plugins/license-manager/js/dashboard-widgets.js',
                ]);
        }
    }
}
