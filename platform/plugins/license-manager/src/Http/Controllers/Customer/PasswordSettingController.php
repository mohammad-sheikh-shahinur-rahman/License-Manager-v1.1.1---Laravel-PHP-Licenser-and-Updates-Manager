<?php

namespace Botble\LicenseManager\Http\Controllers\Customer;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\LicenseManager\Http\Requests\Customer\PasswordRequest;
use Illuminate\Support\Facades\Auth;

class PasswordSettingController extends BaseController
{
    public function __invoke(PasswordRequest $request, BaseHttpResponse $response)
    {
        $customer = Auth::user();

        $customer->forceFill([
            'password' => $request->input('new_password'),
        ])->save();

        return $response
            ->setMessage(trans('plugins/license-manager::customer.password_form.success'));
    }
}
