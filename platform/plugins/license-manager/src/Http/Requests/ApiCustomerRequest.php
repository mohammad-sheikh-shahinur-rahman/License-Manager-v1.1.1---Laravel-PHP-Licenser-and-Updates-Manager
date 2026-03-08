<?php

namespace Botble\LicenseManager\Http\Requests;

use Botble\LicenseManager\Models\Customer;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class ApiCustomerRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => [
                Rule::requiredIf(fn () => ! $this->route('customer')),
                'string',
                'max:120',
                'min:2',
            ],
            'email' => [
                Rule::requiredIf(fn () => ! $this->route('customer')),
                'email',
                'max:60',
                Rule::unique(Customer::class, 'email')->ignore($this->route('customer')),
            ],
            'client_id' => [
                'nullable',
                'string',
                'max:60',
                'min:6',
                Rule::unique(Customer::class, 'client_id')->ignore($this->route('customer')),
            ],
        ];
    }
}
