<?php

namespace Botble\LicenseManager\Http\Controllers;

use Botble\LicenseManager\Models\ActivityLog;
use Botble\LicenseManager\Models\ProductActivation;

class ActivatedProductActivationController extends LicenseManagerController
{
    public function store(ProductActivation $productActivation)
    {
        $productActivation->update([
            'is_active' => true,
        ]);

        ActivityLog::log(trans('plugins/license-manager::license-manager.activity_log.activation_activated_by_admin', [
            'license' => e($productActivation->license_code),
            'url' => e($productActivation->url),
        ]));

        return $this
            ->httpResponse()
            ->setNextUrl(route('lm.activations.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(ProductActivation $productActivation)
    {
        $productActivation->update([
            'is_active' => false,
        ]);

        ActivityLog::log(trans('plugins/license-manager::license-manager.activity_log.activation_deactivated_by_admin', [
            'license' => e($productActivation->license_code),
            'url' => e($productActivation->url),
        ]));

        return $this
            ->httpResponse()
            ->setNextUrl(route('lm.activations.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }
}
