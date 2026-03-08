<?php

namespace Botble\LicenseManager\Http\Requests;

use Botble\Base\Rules\OnOffRule;
use Botble\LicenseManager\Models\Product;
use Botble\LicenseManager\Models\ProductLicense;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class ProductLicenseRequest extends Request
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'type' => $this->input('license_type'),
            'is_valid' => ! $this->boolean('is_valid') ? 1 : 0,
            'uses' => $this->input('uses') ?? 0,
            'domains' => $this->transformTagData($this->input('domains')),
            'ips' => $this->transformTagData($this->input('ips')),
            'bulk_create' => $this->boolean('bulk_create') ? 1 : 0,
            'bulk_quantity' => (int) $this->input('bulk_quantity', 2),
        ]);
    }

    public function rules(): array
    {
        return [
            'product_reference_id' => ['required', 'string', Rule::exists(Product::class, 'reference_id')],
            'license_code' => [
                Rule::requiredIf(! $this->is('api/*') && ! $this->boolean('bulk_create')),
                'nullable',
                'uuid',
                Rule::unique(ProductLicense::class, 'license_code')->ignore($this->route('license')),
            ],
            'bulk_create' => ['sometimes', 'boolean'],
            'bulk_quantity' => ['required_if:bulk_create,1', 'integer', 'min:2', 'max:500'],
            'type' => ['nullable', 'string', 'max:200'],
            'invoice' => ['nullable', 'string', 'max:200'],
            'customer_id' => ['nullable', 'string', 'max:155'],
            'email' => ['nullable', 'email', 'max:155'],
            'uses' => ['nullable', 'numeric', 'min:0'],
            'parallel_uses' => ['nullable', 'numeric', 'min:0'],
            'expires_at' => ['nullable', 'date'],
            'expiry_days' => ['nullable', 'numeric', 'min:0'],
            'updates_until' => ['nullable', 'date'],
            'support_until' => ['nullable', 'date'],
            'domains.*' => ['nullable', 'string'],
            'ips.*' => ['nullable', 'ip'],
            'comments' => ['nullable', 'string', 'max:10000'],
            'is_valid' => [new OnOffRule()],
        ];
    }

    protected function transformTagData(null|string|array $data): array
    {
        if (! $data) {
            return [];
        }

        return array_map(fn ($item) => is_array($data) ? $item : $item['value'], array_filter(is_array($data) ? $data : json_decode($data, true) ?? []));
    }
}
