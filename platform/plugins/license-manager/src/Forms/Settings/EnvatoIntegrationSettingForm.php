<?php

namespace Botble\LicenseManager\Forms\Settings;

use Botble\Base\Facades\PageTitle;
use Botble\LicenseManager\Enums\EnvatoSite;
use Botble\LicenseManager\Http\Requests\Settings\GeneralSettingRequest;
use Botble\Setting\Forms\SettingForm;

class EnvatoIntegrationSettingForm extends SettingForm
{
    public function setup(): void
    {
        parent::setup();

        $this
            ->setValidatorClass(GeneralSettingRequest::class)
            ->setSectionTitle(PageTitle::getTitle(false))
            ->setSectionDescription(
                trans('plugins/license-manager::license-manager.license.description')
            );

        $this
            ->add($name = 'lm_envato_owner_username', 'text', [
                'label' => trans('plugins/license-manager::license-manager.license.form.owner'),
                'value' => old($name, setting($name)),
                'required' => true,
            ])
            ->add($name = 'lm_envato_marketplace', 'customSelect', [
                'label' => trans('plugins/license-manager::license-manager.license.form.marketplace'),
                'choices' => collect(EnvatoSite::cases())->mapWithKeys(function (EnvatoSite $envatoSite) {
                    return [$envatoSite->value => $envatoSite->name];
                })->all(),
                'selected' => old($name, setting($name)),
            ]);
    }
}
