<?php

namespace Botble\LicenseManager\Http\Controllers\Customer\Auth;

use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Controllers\BaseController;
use Botble\LicenseManager\Forms\Auth\LoginForm;
use Botble\LicenseManager\Http\Requests\Customer\Auth\LoginRequest;
use Botble\LicenseManager\Models\CustomerActivityLog;
use Botble\LicenseManager\Support\Helper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class AuthenticatedSessionController extends BaseController
{
    public function index(FormBuilder $formBuilder)
    {
        $this->pageTitle(trans('plugins/license-manager::customer.auth.login'));

        $form = $formBuilder->create(LoginForm::class);

        return view(Helper::viewPath('customer.auth.login'), compact('form'));
    }

    public function store(LoginRequest $request)
    {
        $request->authenticate();

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        $customer = Auth::guard('lm_customer')->user();

        $customer->last_login_at = Carbon::now();
        $customer->save();

        if ($customer->client_id) {
            CustomerActivityLog::query()->create([
                'customer_id' => $customer->client_id,
                'type' => 'login',
                'message' => trans('plugins/license-manager::license-manager.activity_log.customer_login', ['ip' => e($request->ip())]),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent() ?: '',
            ]);
        }

        RateLimiter::clear($request->throttleKey());

        return redirect()->route('lm.customer.dashboard');
    }

    public function destroy(Request $request)
    {
        Auth::guard('lm_customer')->logout();

        $request->session()->regenerate();

        return redirect()->route('lm.customer.auth.login');
    }
}
