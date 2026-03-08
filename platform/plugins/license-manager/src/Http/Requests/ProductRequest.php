<?php

namespace Botble\LicenseManager\Http\Requests;

use Botble\Base\Rules\OnOffRule;
use Botble\LicenseManager\Models\Product;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class ProductRequest extends Request
{
    public function rules(): array
    {
        return [
            'reference_id' => [
                Rule::requiredIf(fn () => ! $this->route('product')),
                'string',
                'max:50',
                Rule::unique(Product::class, 'reference_id')->ignore($this->route('product')),
            ],
            'envato_id' => ['nullable', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'license_update' => [new OnOffRule()],
            'serve_latest_updates' => [new OnOffRule()],
            'is_active' => ['required', 'boolean'],
            'default_license_type' => ['nullable', 'string', 'max:50'],
            'default_uses' => ['nullable', 'integer', 'min:0'],
            'default_parallel_uses' => ['nullable', 'integer', 'min:0'],
            'default_expiry_days' => ['nullable', 'integer', 'min:0'],
            'default_updates_until_days' => ['nullable', 'integer', 'min:0'],
            'default_support_until_days' => ['nullable', 'integer', 'min:0'],
            'default_comments' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
