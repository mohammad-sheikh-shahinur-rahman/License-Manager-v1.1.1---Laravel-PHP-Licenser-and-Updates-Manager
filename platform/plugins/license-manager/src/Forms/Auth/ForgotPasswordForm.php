<?php

namespace Botble\LicenseManager\Forms\Auth;

use Botble\Base\Facades\Html;
use Botble\LicenseManager\Http\Requests\Customer\Auth\ForgotPasswordRequest;
use Illuminate\Support\Facades\Blade;

class ForgotPasswordForm extends AuthForm
{
    public function buildForm(): void
    {
        parent::buildForm();

        $this
            ->setValidatorClass(ForgotPasswordRequest::class)
            ->setUrl(route('lm.customer.auth.password.email'))
            ->add('email', 'text', [
                'label' => trans('plugins/license-manager::customer.auth.email'),
                'value' => old('email'),
                'attr' => [
                    'placeholder' => trans('plugins/license-manager::customer.auth.email'),
                ],
            ])
            ->addCaptchaFieldsWhenAvailable()
            ->add('button_submit', 'html', [
                'html' => Blade::render(
                    sprintf(
                        '<x-core::button type="submit" color="primary" class="w-full" icon="%s">%s</x-core::button>',
                        'ti ti-mail',
                        trans('plugins/license-manager::customer.auth.submit'),
                    )
                ),
            ])
            ->add('back_to_login_page', 'html', [
                'html' => sprintf(
                    '<div class="text-center mt-3">%s</div>',
                    Html::link(route('lm.customer.auth.login'), trans('plugins/license-manager::customer.auth.back_to_login_page'))
                ),
            ]);
    }
}
