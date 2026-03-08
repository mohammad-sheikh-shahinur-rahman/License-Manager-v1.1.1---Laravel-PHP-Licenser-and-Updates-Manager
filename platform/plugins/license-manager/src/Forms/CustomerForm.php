<?php

namespace Botble\LicenseManager\Forms;

use Botble\Base\Forms\FormAbstract;
use Botble\LicenseManager\Http\Requests\CustomerCreateRequest;
use Botble\LicenseManager\Models\Customer;

class CustomerForm extends FormAbstract
{
    public function buildForm(): void
    {
        $this
            ->setupModel(new Customer())
            ->setValidatorClass(CustomerCreateRequest::class)
            ->withCustomFields()
            ->add('name', 'text', [
                'label' => trans('plugins/license-manager::license-manager.customers.form.name'),
                'required' => true,
                'attr' => [
                    'placeholder' => trans('plugins/license-manager::license-manager.customers.form.name_placeholder'),
                    'data-counter' => 120,
                ],
            ])
            ->add('email', 'text', [
                'label' => trans('plugins/license-manager::license-manager.customers.form.email'),
                'required' => true,
                'attr' => [
                    'placeholder' => trans('plugins/license-manager::license-manager.customers.form.email_placeholder'),
                    'data-counter' => 60,
                ],
            ])
            ->add('client_id', 'text', [
                'label' => trans('plugins/license-manager::license-manager.customers.form.client_id'),
                'help_block' => [
                    'text' => trans('plugins/license-manager::license-manager.customers.form.client_id_helper'),
                ],
                'attr' => [
                    'placeholder' => trans('plugins/license-manager::license-manager.customers.form.client_id_placeholder'),
                    'data-counter' => 60,
                ],
            ]);

        if ($id = $this->getModel()->id) {
            $this->add('is_change_password', 'onOffCheckbox', [
                'label' => trans('plugins/license-manager::license-manager.customers.form.change_password'),
                'attr' => [
                    'data-bb-toggle' => 'collapse',
                    'data-bb-target' => '.change-password',
                ],
            ]);
        }
        $this->add('open_wrapper_password_form', 'html', [
            'html' => sprintf('<div class="change-password" data-bb-value="1" style="display: %s">', ! $id ? 'block' : 'none'),
        ]);
        $this->add('password', 'password', [
            'label' => trans('plugins/license-manager::license-manager.customers.form.password'),
            'required' => true,
            'attr' => [
                'placeholder' => trans('plugins/license-manager::license-manager.customers.form.password'),
                'data-counter' => 60,
            ],
        ])
        ->add('password_confirmation', 'password', [
            'label' => trans('plugins/license-manager::license-manager.customers.form.password_confirmation'),
            'required' => true,
            'attr' => [
                'placeholder' => trans('plugins/license-manager::license-manager.customers.form.password_confirmation'),
                'data-counter' => 60,
            ],
        ]);
        $this->add('close_wrapper_password_form', 'html', [
            'html' => '</div>',
        ]);
    }
}
