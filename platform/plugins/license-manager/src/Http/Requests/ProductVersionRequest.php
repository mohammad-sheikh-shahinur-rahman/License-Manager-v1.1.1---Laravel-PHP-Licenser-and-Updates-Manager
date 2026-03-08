<?php

namespace Botble\LicenseManager\Http\Requests;

use Botble\Base\Rules\OnOffRule;
use Botble\LicenseManager\Models\ProductVersion;
use Botble\LicenseManager\Support\Helper;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class ProductVersionRequest extends Request
{
    public function rules(): array
    {
        return [
            'version_id' => [
                Rule::requiredIf(fn () => ! $this->is('api/*') && ! $this->route('version')),
                'string',
                'max:50',
                Rule::unique(ProductVersion::class, 'version_id')->ignore($this->route('version')),
            ],
            'version' => [
                'required',
                'string',
                'regex:/^v?\d+\.\d+(\.\d+)?(-[\w-]+)?$/',
                Rule::unique(ProductVersion::class, 'version')
                    ->where('product_reference_id', $this->route('product')?->reference_id)
                    ->ignore($this->route('version')),
            ],
            'released_at' => ['required', 'date'],
            'summary' => ['nullable', 'string', 'max:255'],
            'changelog' => ['required', 'string', 'max:100000'],
            'main_file' => [
                Rule::requiredIf(! $this->route('version')),
                'file',
                'mimes:zip,gz,tar.gz,tar.bz2,tar.xz',
            ],
            'sql_file' => ['nullable', 'file', 'mimes:' . implode(',', Helper::getSupportedFileTypes())],
            'delete_old_versions' => ['nullable', 'string', 'in:no,yes'],
            'version_status' => ['string', 'in:on,off'],
            'is_active' => ['string', new OnOffRule()],
        ];
    }
}
