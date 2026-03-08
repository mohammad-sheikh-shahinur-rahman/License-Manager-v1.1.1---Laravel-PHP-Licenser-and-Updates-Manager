<?php

namespace Botble\LicenseManager\Forms\Auth;

use Botble\LicenseManager\Http\Requests\Customer\Auth\ResetPasswordRequest;
use Illuminate\Support\Facades\Blade;

class ResetPasswordForm extends AuthForm
{
    public function buildForm(): void
    {
        parent::buildForm();

        $this
            ->setUrl(route('lm.customer.auth.password.store'))
            ->setValidatorClass(ResetPasswordRequest::class)
            ->add('token', 'hidden', [
                'value' => $this->request->route('token'),
            ])
            ->add('email', 'text', [
                'label' => trans('plugins/license-manager::customer.auth.email'),
                'value' => old('email', $this->request->input('email')),
                'required' => true,
                'attr' => [
                    'placeholder' => trans('plugins/license-manager::customer.auth.email'),
                ],
            ])
            ->add('password', 'password', [
                'label' => trans('plugins/license-manager::customer.auth.new_password'),
                'required' => true,
                'attr' => [
                    'placeholder' => trans('plugins/license-manager::customer.auth.new_password'),
                ],
            ])
            ->add('password_confirmation', 'password', [
                'label' => trans('plugins/license-manager::customer.auth.confirm_new_password'),
                'required' => true,
                'attr' => [
                    'placeholder' => trans('plugins/license-manager::customer.auth.confirm_new_password'),
                ],
            ])
            ->add('button_submit', 'html', [
                'html' => Blade::render(
                    sprintf(
                        '<x-core::button type="submit" color="primary" class="w-full mt-3">%s</x-core::button>',
                        trans('plugins/license-manager::customer.auth.update'),
                    )
                ),
            ]);
    }
}
