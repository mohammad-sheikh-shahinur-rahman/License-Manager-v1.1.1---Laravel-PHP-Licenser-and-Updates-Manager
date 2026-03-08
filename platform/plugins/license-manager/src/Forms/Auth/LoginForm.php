<?php

namespace Botble\LicenseManager\Forms\Auth;

use Botble\Base\Facades\Html;
use Botble\LicenseManager\Http\Requests\Customer\Auth\LoginRequest;
use Botble\LicenseManager\Support\Helper;
use Botble\SocialLogin\Facades\SocialService;
use Illuminate\Support\Facades\Blade;

class LoginForm extends AuthForm
{
    public function buildForm(): void
    {
        parent::buildForm();

        $this
            ->setValidatorClass(LoginRequest::class)
            ->setUrl(route('lm.customer.auth.login.store'));

        $this->addSocialLoginOptions();

        $this
            ->add('email', 'text', [
                'label' => trans('plugins/license-manager::customer.auth.email'),
                'value' => old('email'),
                'required' => true,
                'attr' => [
                    'placeholder' => trans('plugins/license-manager::customer.auth.email'),
                ],
            ])
            ->add('password', 'password', [
                'label' => trans('plugins/license-manager::customer.auth.password'),
                'value' => old('password'),
                'required' => true,
                'attr' => [
                    'placeholder' => trans('plugins/license-manager::customer.auth.password'),
                ],
            ])
            ->add('open_wrapper_login_form', 'html', [
                'html' => '<div class="d-flex justify-content-between">',
            ])
            ->add('remember', 'onOffCheckbox', [
                'label' => trans('plugins/license-manager::customer.auth.remember_me'),
                'wrapper' => [
                    'class' => 'mb-0',
                ],
            ])
            ->add('forgot_password', 'html', [
                'html' =>
                    Html::link(
                        route('lm.customer.auth.password.request'),
                        trans('plugins/license-manager::customer.auth.forgot_your_password'),
                    ),
            ])
            ->add('close_wrapper_login_form', 'html', [
                'html' => '</div>',
            ])
            ->addCaptchaFieldsWhenAvailable()
            ->add('button_submit', 'html', [
                'html' => Blade::render(
                    sprintf(
                        '<x-core::button type="submit" color="primary" class="w-full" icon="%s">%s</x-core::button>',
                        'ti ti-login-2',
                        trans('plugins/license-manager::customer.auth.login'),
                    )
                ),
            ]);
    }

    protected function addSocialLoginOptions(): void
    {
        if (! is_plugin_active('social-login') || ! SocialService::hasAnyProviderEnable()) {
            return;
        }

        $this
            ->add('login-via-social', 'html', [
                'html' => view(Helper::viewPath('customer.auth.partials.social-login-options'))->render(),
            ])
            ->add('separation', 'html', [
                'html' => Html::tag('div', trans('plugins/license-manager::customer.auth.or_login_with'), [
                    'class' => 'text-center text-muted mb-2',
                ]),
            ]);
    }
}
