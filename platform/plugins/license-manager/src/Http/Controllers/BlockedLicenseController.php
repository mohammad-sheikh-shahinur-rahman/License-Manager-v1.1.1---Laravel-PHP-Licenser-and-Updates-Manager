<?php

namespace Botble\LicenseManager\Http\Controllers;

use Botble\LicenseManager\Models\ActivityLog;
use Botble\LicenseManager\Models\ProductLicense;

class BlockedLicenseController extends LicenseManagerController
{
    public function store(ProductLicense $license)
    {
        $license->update([
            'is_valid' => false,
        ]);

        ActivityLog::log(trans('plugins/license-manager::license-manager.activity_log.license_blocked_by_admin', ['license' => e($license->license_code)]));

        return $this
            ->httpResponse()
            ->setNextUrl(route('lm.licenses.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(ProductLicense $license)
    {
        $license->update([
            'is_valid' => true,
        ]);

        ActivityLog::log(trans('plugins/license-manager::license-manager.activity_log.license_unblocked_by_admin', ['license' => e($license->license_code)]));

        return $this
            ->httpResponse()
            ->setNextUrl(route('lm.licenses.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }
}
