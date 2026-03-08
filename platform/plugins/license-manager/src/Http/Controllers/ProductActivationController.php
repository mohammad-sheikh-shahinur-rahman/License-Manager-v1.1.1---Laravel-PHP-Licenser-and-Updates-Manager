<?php

namespace Botble\LicenseManager\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Supports\Breadcrumb;
use Botble\LicenseManager\Models\ActivityLog;
use Botble\LicenseManager\Models\ProductActivation;
use Botble\LicenseManager\Tables\ProductActivationTable;

class ProductActivationController extends LicenseManagerController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/license-manager::license-manager.activations.title'), route('lm.activations.index'));
    }

    public function index(ProductActivationTable $activationTable)
    {
        $this->pageTitle(trans('plugins/license-manager::license-manager.activations.title'));

        return $activationTable->renderTable();
    }

    public function destroy(ProductActivation $productActivation)
    {
        ActivityLog::log(trans('plugins/license-manager::license-manager.activity_log.activation_deleted_by_admin', [
            'license' => e($productActivation->license_code),
            'url' => e($productActivation->url),
        ]));

        return DeleteResourceAction::make($productActivation);
    }
}
