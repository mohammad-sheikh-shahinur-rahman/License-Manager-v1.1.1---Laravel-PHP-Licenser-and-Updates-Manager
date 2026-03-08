<?php

namespace Botble\LicenseManager\Http\Controllers\Settings;

use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Supports\Breadcrumb;
use Botble\LicenseManager\Forms\Settings\GeneralSettingForm;
use Botble\LicenseManager\Http\Requests\Settings\GeneralSettingRequest;
use Botble\LicenseManager\LicenseManager;
use Botble\LicenseManager\Models\ActivityLog;
use Botble\Setting\Facades\Setting;
use Botble\Setting\Http\Controllers\SettingController;

class GeneralSettingController extends SettingController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/license-manager::license-manager.title'));
    }

    public function edit()
    {
        $this->pageTitle(trans('plugins/license-manager::license-manager.general.title'));

        $form = GeneralSettingForm::create();

        return $form->renderForm();
    }

    public function update(GeneralSettingRequest $request, LicenseManager $licenseManager)
    {
        $data = $request->validated();

        $cipherChanged = $request->input('lm_license_encryption_cipher') !== setting('lm_license_encryption_cipher', 'aes-256-cbc');

        if ($cipherChanged || ! setting('lm_license_encryption_key')) {
            $data['lm_license_encryption_key'] = $licenseManager->generateEncryptionKey(
                $request->input('lm_license_encryption_cipher')
            );
        }

        return $this->performUpdate($data);
    }

    public function regenerateEncryptionKey(LicenseManager $licenseManager): BaseHttpResponse
    {
        $cipher = setting('lm_license_encryption_cipher', 'aes-256-cbc');
        $newKey = $licenseManager->generateEncryptionKey($cipher);

        Setting::set(['lm_license_encryption_key' => $newKey])->save();

        ActivityLog::log(trans('plugins/license-manager::license-manager.general.license_encryption_key_regenerated_log'));

        return $this
            ->httpResponse()
            ->setMessage(trans('plugins/license-manager::license-manager.general.license_encryption_key_regenerated'));
    }
}
