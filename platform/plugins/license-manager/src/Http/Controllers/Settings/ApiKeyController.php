<?php

namespace Botble\LicenseManager\Http\Controllers\Settings;

use Botble\Base\Supports\Breadcrumb;
use Botble\LicenseManager\Forms\Settings\ApiKeyForm;
use Botble\LicenseManager\Http\Requests\Settings\ApiKeyRequest;
use Botble\LicenseManager\Models\ApiKey;
use Botble\Setting\Http\Controllers\SettingController;

class ApiKeyController extends SettingController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/license-manager::license-manager.title'))
            ->add(trans('plugins/license-manager::license-manager.api_setting.title'), route('lm.api.settings'));
    }

    public function create()
    {
        $this->pageTitle(trans('core/base::forms.create'));

        return ApiKeyForm::create()->renderForm();
    }

    public function store(ApiKeyRequest $request)
    {
        $form = ApiKeyForm::create()
            ->setRequest($request);

        $form->save();

        return $this
            ->httpResponse()
            ->setPreviousRoute('lm.api.settings')
            ->setNextRoute('lm.api-keys.edit', $form->getModel()->getKey())
            ->withCreatedSuccessMessage();
    }

    public function edit(ApiKey $apiKey)
    {
        $this->pageTitle(trans('core/base::forms.edit_item', ['name' => $apiKey->key]));

        return ApiKeyForm::createFromModel($apiKey)->renderForm();
    }

    public function update(ApiKey $apiKey, ApiKeyRequest $request)
    {
        $form = ApiKeyForm::createFromModel($apiKey)
            ->setRequest($request);

        $form->save();

        return $this
            ->httpResponse()
            ->setPreviousRoute('lm.api.settings')
            ->setNextRoute('lm.api-keys.edit', $apiKey->getKey())
            ->withUpdatedSuccessMessage();
    }

    public function destroy(ApiKey $apiKey)
    {
        $apiKey->delete();

        return $this
            ->httpResponse()
            ->withDeletedSuccessMessage();
    }
}
