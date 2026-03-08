<?php

namespace Botble\LicenseManager\Http\Controllers\Api\Internal;

use Botble\Base\Facades\EmailHandler;
use Botble\Base\Http\Controllers\BaseController;
use Botble\LicenseManager\Http\Requests\ApiCustomerRequest;
use Botble\LicenseManager\Http\Resources\CustomerJsonResource;
use Botble\LicenseManager\Models\ActivityLog;
use Botble\LicenseManager\Models\Customer;
use Botble\LicenseManager\Notifications\CustomerWelcomeNotification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Throwable;

class CustomerController extends BaseController
{
    public function index(): AnonymousResourceCollection
    {
        $customers = Customer::query()->paginate();

        return CustomerJsonResource::collection($customers);
    }

    public function show(string $customerId): JsonResponse|CustomerJsonResource
    {
        $customer = Customer::query()->find($customerId);

        if (! $customer) {
            return new JsonResponse([
                'message' => trans('plugins/license-manager::license-manager.api.internal.customers.not_found'),
            ]);
        }

        return CustomerJsonResource::make($customer);
    }

    public function store(ApiCustomerRequest $request): CustomerJsonResource
    {
        $customer = Customer::query()->create([
            ...$request->validated(),
            'password' => Str::random(32),
            'confirmed_at' => Carbon::now(),
        ]);

        try {
            if (EmailHandler::setModule('license-manager')->templateEnabled('customer-welcome')) {
                $token = Password::broker('lm_customers')->createToken($customer);
                $customer->notify(new CustomerWelcomeNotification($token));
            }
        } catch (Throwable) {
        }

        ActivityLog::log(trans('plugins/license-manager::license-manager.activity_log.customer_created_via_api', ['customer' => e($customer->name)]));

        return CustomerJsonResource::make($customer);
    }

    public function update(ApiCustomerRequest $request, string $customerId): JsonResponse|CustomerJsonResource
    {
        $customer = Customer::query()->find($customerId);

        if (! $customer) {
            return new JsonResponse([
                'message' => trans('plugins/license-manager::license-manager.api.internal.customers.not_found'),
            ]);
        }

        $customer->update($request->validated());

        ActivityLog::log(trans('plugins/license-manager::license-manager.activity_log.customer_updated_via_api', ['customer' => e($customer->name)]));

        return CustomerJsonResource::make($customer);
    }
}
