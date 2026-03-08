<?php

namespace Botble\LicenseManager\Http\Controllers\Customer;

use Botble\Base\Http\Controllers\BaseController;
use Botble\LicenseManager\Tables\CustomerProductActivationTable;

class ProductActivationController extends BaseController
{
    public function index(CustomerProductActivationTable $customerProductActivationTable)
    {
        abort_unless(setting('lm_customer_page_activations', true), 404);

        $this->pageTitle(
            trans('plugins/license-manager::license-manager.customers.product_activations')
        );

        return $customerProductActivationTable->renderTable();
    }
}
