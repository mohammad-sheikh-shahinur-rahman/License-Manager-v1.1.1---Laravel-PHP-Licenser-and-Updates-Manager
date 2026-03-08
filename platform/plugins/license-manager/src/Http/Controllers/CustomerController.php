<?php

namespace Botble\LicenseManager\Http\Controllers;

use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Supports\Breadcrumb;
use Botble\LicenseManager\Forms\CustomerForm;
use Botble\LicenseManager\Http\Requests\CustomerCreateRequest;
use Botble\LicenseManager\Http\Requests\CustomerEditRequest;
use Botble\LicenseManager\Models\ActivityLog;
use Botble\LicenseManager\Models\Customer;
use Botble\LicenseManager\Tables\CustomerTable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class CustomerController extends LicenseManagerController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/license-manager::license-manager.customers.title'), route('lm.customers.index'));
    }

    public function index(CustomerTable $dataTable)
    {
        $this->pageTitle(trans('plugins/license-manager::license-manager.customers.title'));

        return $dataTable->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        $this->pageTitle(trans('plugins/license-manager::license-manager.customers.form.create'));

        return $formBuilder->create(CustomerForm::class)->renderForm();
    }

    public function store(CustomerCreateRequest $request, BaseHttpResponse $response)
    {
        $customer = Customer::query()->create([
            ...Arr::except($request->validated(), 'password'),
            'password' => Hash::make($request->input('password')),
        ]);

        ActivityLog::log(trans('plugins/license-manager::license-manager.activity_log.customer_created_by_admin', ['customer' => e($customer->name)]));

        return $response
            ->setNextRoute('lm.customers.index')
            ->setPreviousRoute('lm.customers.edit', $customer->getKey())
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(Customer $customer, FormBuilder $formBuilder)
    {
        $this->pageTitle(trans('plugins/license-manager::license-manager.customers.form.edit', ['name' => $customer->name]));

        $customer->password = null;

        return $formBuilder->create(CustomerForm::class, ['model' => $customer])->renderForm();
    }

    public function update(Customer $customer, CustomerEditRequest $request, BaseHttpResponse $response)
    {
        $customer->fill(Arr::except($request->validated(), 'password'));

        if ($request->boolean('is_change_password')) {
            $customer->password = $request->input('password');
        }

        $customer->save();

        ActivityLog::log(trans('plugins/license-manager::license-manager.activity_log.customer_updated_by_admin', ['customer' => e($customer->name)]));

        return $response
            ->setNextUrl(route('lm.customers.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(Customer $customer)
    {
        ActivityLog::log(trans('plugins/license-manager::license-manager.activity_log.customer_deleted_by_admin', ['customer' => e($customer->name)]));

        return DeleteResourceAction::make($customer);
    }
}
