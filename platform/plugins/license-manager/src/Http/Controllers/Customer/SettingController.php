<?php

namespace Botble\LicenseManager\Http\Controllers\Customer;

use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Controllers\BaseController;
use Botble\LicenseManager\Forms\CustomerBasicSettingForm;
use Botble\LicenseManager\Forms\CustomerPasswordSettingForm;
use Botble\LicenseManager\Support\Helper;
use Illuminate\Http\Request;

class SettingController extends BaseController
{
    public function index(Request $request, FormBuilder $formBuilder)
    {
        $customer = $request->user();

        $basicSettingForm = $formBuilder->create(CustomerBasicSettingForm::class, ['model' => $customer]);

        $passwordSettingForm = $formBuilder->create(CustomerPasswordSettingForm::class);

        return view(
            Helper::viewPath('customer.settings.index'),
            compact('basicSettingForm', 'passwordSettingForm')
        );
    }
}
