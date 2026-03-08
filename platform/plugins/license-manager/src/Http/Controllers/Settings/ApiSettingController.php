<?php

namespace Botble\LicenseManager\Http\Controllers\Settings;

use Botble\Base\Supports\Breadcrumb;
use Botble\LicenseManager\Forms\Settings\ApiSettingForm;
use Botble\LicenseManager\Http\Requests\Settings\ApiSettingRequest;
use Botble\Setting\Http\Controllers\SettingController;

class ApiSettingController extends SettingController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/license-manager::license-manager.title'));
    }

    public function edit()
    {
        $this->pageTitle(trans('plugins/license-manager::license-manager.api_setting.title'));

        return ApiSettingForm::create()->renderForm();
    }

    public function update(ApiSettingRequest $request)
    {
        return $this->performUpdate($request->validated());
    }
}
