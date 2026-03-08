<?php

namespace Botble\LicenseManager\Http\Controllers\Customer\Auth;

use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\LicenseManager\Forms\Auth\ResetPasswordForm;
use Botble\LicenseManager\Http\Requests\Customer\Auth\ResetPasswordRequest;
use Botble\LicenseManager\Support\Helper;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class NewPasswordController extends BaseController
{
    public function create(Request $request, FormBuilder $formBuilder)
    {
        $this->pageTitle(trans('plugins/license-manager::customer.auth.reset_password'));

        $form = $formBuilder->create(ResetPasswordForm::class);

        return view(Helper::viewPath('customer.auth.reset-password'), compact('request', 'form'));
    }

    public function store(ResetPasswordRequest $request)
    {
        $status = Password::broker('lm_customers')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request): void {
                $user->forceFill([
                    'password' => $request->input('password'),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        $response = BaseHttpResponse::make();

        if ($status != Password::PASSWORD_RESET) {
            return $response->setError()->setMessage(match ($status) {
                Password::RESET_THROTTLED => trans('plugins/license-manager::customer.auth.reset_password_messages.reset_throttled'),
                Password::INVALID_TOKEN => trans('plugins/license-manager::customer.auth.reset_password_messages.invalid_token'),
                Password::INVALID_USER => trans('plugins/license-manager::customer.auth.reset_password_messages.invalid_user'),
                default => trans('plugins/license-manager::customer.auth.reset_password_messages.something_went_wrong'),
            });
        }

        return $response
            ->setNextUrl(route('lm.customer.auth.login'))
            ->setMessage(trans('plugins/license-manager::customer.auth.reset_password_messages.password_reset_success'));
    }
}
