<?php

namespace Botble\LicenseManager\Forms;

use Botble\Base\Forms\FormAbstract;
use Botble\LicenseManager\Http\Requests\Customer\PasswordRequest;
use Botble\LicenseManager\Models\Customer;

class CustomerPasswordSettingForm extends FormAbstract
{
    public function buildForm(): void
    {
        $this
            ->setupModel(new Customer())
            ->contentOnly()
            ->setValidatorClass(PasswordRequest::class)
            ->setUrl(route('lm.customer.settings.password'))
            ->withCustomFields()
            ->add('password', 'password', [
                'label' => trans('plugins/license-manager::customer.password_form.old_password'),
                'required' => true,
                'attr' => [
                    'placeholder' => trans('plugins/license-manager::customer.password_form.old_password'),
                    'data-counter' => 60,
                ],
            ])
            ->add('new_password', 'password', [
                'label' => trans('plugins/license-manager::customer.password_form.new_password'),
                'required' => true,
                'attr' => [
                    'placeholder' => trans('plugins/license-manager::customer.password_form.new_password'),
                    'data-counter' => 60,
                ],
            ])
            ->add('new_password_confirmation', 'password', [
                'label' => trans('plugins/license-manager::customer.password_form.new_password_confirmation'),
                'required' => true,
                'attr' => [
                    'placeholder' => trans('plugins/license-manager::customer.password_form.new_password_confirmation'),
                    'data-counter' => 60,
                ],
            ])
            ->add('actions', 'html', [
                'html' => view('core/acl::users.profile.actions')->render(),
            ]);
    }
}
