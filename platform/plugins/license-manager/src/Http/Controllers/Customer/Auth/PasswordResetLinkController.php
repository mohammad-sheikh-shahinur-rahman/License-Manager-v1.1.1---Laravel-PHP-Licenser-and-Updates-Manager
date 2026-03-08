<?php

namespace Botble\LicenseManager\Http\Controllers\Customer\Auth;

use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\LicenseManager\Forms\Auth\ForgotPasswordForm;
use Botble\LicenseManager\Http\Requests\Customer\Auth\ForgotPasswordRequest;
use Botble\LicenseManager\Support\Helper;
use Illuminate\Support\Facades\Password;

class PasswordResetLinkController extends BaseController
{
    public function create(FormBuilder $formBuilder)
    {
        $this->pageTitle(trans('plugins/license-manager::customer.auth.forgot_password.title'));

        $form = $formBuilder->create(ForgotPasswordForm::class);

        return view(Helper::viewPath('customer.auth.forgot-password'), compact('form'));
    }

    public function store(ForgotPasswordRequest $request, BaseHttpResponse $response)
    {
        $status = Password::broker('lm_customers')->sendResetLink($request->only('email'));

        if ($status != Password::RESET_LINK_SENT) {
            return $response->setError()->setMessage(match ($status) {
                Password::RESET_THROTTLED => trans('plugins/license-manager::customer.auth.forgot_password.reset_throttled'),
                Password::INVALID_USER => trans('plugins/license-manager::customer.auth.forgot_password.invalid_user'),
                default => trans('plugins/license-manager::customer.auth.forgot_password.something_went_wrong'),
            });
        }

        return $response
            ->setNextUrl(route('lm.customer.auth.password.request'))
            ->setMessage(trans('plugins/license-manager::customer.auth.forgot_password.emailed_reset_link'));
    }
}
