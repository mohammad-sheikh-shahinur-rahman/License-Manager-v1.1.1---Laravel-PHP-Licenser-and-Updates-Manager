<?php

namespace Botble\LicenseManager\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Supports\Breadcrumb;

abstract class LicenseManagerController extends BaseController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/license-manager::license-manager.title'));
    }
}
