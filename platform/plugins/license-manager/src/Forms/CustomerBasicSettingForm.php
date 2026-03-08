<?php

namespace Botble\LicenseManager\Forms;

use Botble\Base\Forms\FormAbstract;
use Botble\LicenseManager\Http\Requests\Customer\ProfileRequest;
use Botble\LicenseManager\Models\Customer;

class CustomerBasicSettingForm extends FormAbstract
{
    public function buildForm(): void
    {
        $this
            ->setupModel(new Customer())
            ->setValidatorClass(ProfileRequest::class)
            ->contentOnly()
            ->withCustomFields()
            ->setUrl(route('lm.customer.settings.basic'))
            ->add('name', 'text', [
                'label' => trans('plugins/license-manager::customer.basic_form.name'),
                'required' => true,
                'attr' => [
                    'data-counter' => 120,
                ],
            ])
            ->when($this->getModel()->client_id, function (): void {
                $this->add('client_id', 'text', [
                    'label' => trans('plugins/license-manager::customer.basic_form.client_id'),
                    'attr' => [
                        'disabled' => true,
                    ],
                    'help_block' => [
                        'tag' => 'span',
                        'text' => trans('plugins/license-manager::customer.basic_form.client_id_description'),
                    ],
                ]);
            })
            ->add('email', 'text', [
                'label' => trans('plugins/license-manager::customer.basic_form.email'),
                'attr' => [
                    'disabled' => true,
                ],
            ])
            ->add('actions', 'html', [
                'html' => view('core/acl::users.profile.actions')->render(),
            ]);
    }
}
