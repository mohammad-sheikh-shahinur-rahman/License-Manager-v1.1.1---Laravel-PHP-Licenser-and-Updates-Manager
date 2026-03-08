<?php

namespace Botble\SocialLoginEnvato\Providers;

use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\SocialLogin\Facades\SocialService;
use Botble\SocialLoginEnvato\Drivers\EnvatoProvider;
use Laravel\Socialite\Contracts\Factory;

class SocialLoginEnvatoServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function boot(): void
    {
        if (! is_plugin_active('social-login')) {
            return;
        }

        $this
            ->setNamespace('plugins/social-login-envato')
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->publishAssets();

        $this->app->booted(function (): void {
            $this->registerEnvatoDriver();
            $this->registerEnvatoProvider();
            $this->registerEnvatoTokenRefreshEndpoint();
            $this->registerEnvatoStyles();
            $this->registerEnvatoRender();
        });
    }

    protected function registerEnvatoDriver(): void
    {
        if (! interface_exists(Factory::class)) {
            return;
        }

        $socialite = $this->app->make(Factory::class);

        $socialite->extend('envato', function () use ($socialite) {
            $callbackUrl = apply_filters('social_login_callback_url', route('auth.social.callback', 'envato'), 'envato');

            $config = [
                'client_id' => SocialService::setting('envato_app_id'),
                'client_secret' => SocialService::setting('envato_app_secret'),
                'redirect' => $callbackUrl,
            ];

            return $socialite->buildProvider(EnvatoProvider::class, $config);
        });
    }

    protected function registerEnvatoProvider(): void
    {
        add_filter('social_login_providers', function (array $providers): array {
            $callbackUrl = apply_filters('social_login_callback_url', route('auth.social.callback', 'envato'), 'envato');

            $providers['envato'] = [
                'data' => ['app_id', 'app_secret'],
                'disable' => ['app_secret'],
                'label' => [
                    'enable' => trans('plugins/social-login-envato::social-login-envato.enable'),
                    'app_id' => trans('plugins/social-login-envato::social-login-envato.app_id'),
                    'app_secret' => trans('plugins/social-login-envato::social-login-envato.app_secret'),
                    'helper' => trans('plugins/social-login-envato::social-login-envato.helper', [
                        'callback' => '<code class="text-danger">' . $callbackUrl . '</code>',
                    ]),
                ],
            ];

            return $providers;
        }, 20);
    }

    protected function registerEnvatoTokenRefreshEndpoint(): void
    {
        add_filter('social_login_token_refresh_endpoints', function (array $endpoints): array {
            $endpoints['envato'] = 'https://api.envato.com/token';

            return $endpoints;
        }, 20);
    }

    protected function registerEnvatoStyles(): void
    {
        $css = '<style>.login-options .social-icons.social-login-lg{display:flex;flex-direction:column}.login-options .social-icons.social-login-lg li{width:100%}.login-options .social-icons.social-login-lg li .envato{display:flex;align-items:center;justify-content:center;gap:.5rem;width:100%;background-color:#82b541;color:#fff;padding:.75rem 1.5rem;border-radius:8px;font-weight:500;text-decoration:none}.login-options .social-icons.social-login-lg li .envato:hover{background-color:#6a9a35}.login-options .social-icons li .envato{background-color:#82b541;color:#fff}.login-options .social-icons li .envato:hover{background-color:#6a9a35}.login-options .social-icons li .x{background-color:#000;color:#fff}.login-options .social-icons li .x:hover{background-color:#333}</style>';

        add_filter(THEME_FRONT_HEADER, fn (?string $html): string => $html . $css, 20);
        add_filter(BASE_FILTER_HEAD_LAYOUT_TEMPLATE, fn (?string $html): string => $html . $css, 20);
    }

    protected function registerEnvatoRender(): void
    {
        add_filter('social_login_envato_render', function (string $html, string $social): string {
            return view('plugins/social-login-envato::social-login-item', [
                'social' => $social,
                'url' => route('auth.social', $social),
            ])->render();
        }, 20, 2);
    }
}
