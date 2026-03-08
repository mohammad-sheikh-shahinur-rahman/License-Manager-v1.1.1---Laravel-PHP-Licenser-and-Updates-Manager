<?php

namespace Botble\LicenseManager\Http\Requests;

use Botble\LicenseManager\Models\Product;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class BulkLicenseRequest extends Request
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'type' => $this->input('license_type'),
            'uses' => $this->input('uses') ?? 0,
        ]);
    }

    public function rules(): array
    {
        return [
            'product_reference_id' => ['required', 'string', Rule::exists(Product::class, 'reference_id')],
            'quantity' => ['required', 'integer', 'min:2', 'max:500'],
            'type' => ['nullable', 'string', 'max:200'],
            'uses' => ['nullable', 'numeric', 'min:0'],
            'parallel_uses' => ['nullable', 'numeric', 'min:0'],
            'expiry_days' => ['nullable', 'numeric', 'min:0'],
            'expires_at' => ['nullable', 'date'],
            'updates_until' => ['nullable', 'date'],
            'support_until' => ['nullable', 'date'],
            'customer_id' => ['nullable', 'string', 'max:155'],
            'email' => ['nullable', 'email', 'max:155'],
            'invoice' => ['nullable', 'string', 'max:200'],
            'comments' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
