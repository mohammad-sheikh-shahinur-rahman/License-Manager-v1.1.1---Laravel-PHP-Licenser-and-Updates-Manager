<?php

namespace Botble\LicenseManager\Http\Controllers\Customer;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\LicenseManager\Http\Requests\Customer\ProfileRequest;
use Illuminate\Support\Facades\Auth;

class BasicSettingController extends BaseController
{
    public function __invoke(ProfileRequest $request, BaseHttpResponse $response)
    {
        $customer = Auth::user();

        $customer->fill($request->validated())->save();

        return $response
            ->setMessage(trans('plugins/license-manager::customer.basic_form.success'));
    }
}
