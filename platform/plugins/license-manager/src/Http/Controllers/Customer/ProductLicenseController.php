<?php

namespace Botble\LicenseManager\Http\Controllers\Customer;

use Botble\Base\Http\Controllers\BaseController;
use Botble\LicenseManager\Tables\CustomerProductLicenseTable;

class ProductLicenseController extends BaseController
{
    public function index(CustomerProductLicenseTable $customerProductLicenseTable)
    {
        abort_unless(setting('lm_customer_page_licenses', true), 404);

        $this->pageTitle(
            trans('plugins/license-manager::license-manager.customers.product_licenses')
        );

        return $customerProductLicenseTable->renderTable();
    }
}
