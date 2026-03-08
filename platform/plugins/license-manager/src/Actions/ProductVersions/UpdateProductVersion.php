<?php

namespace Botble\LicenseManager\Actions\ProductVersions;

use Botble\LicenseManager\Models\Product;
use Botble\LicenseManager\Models\ProductVersion;
use Illuminate\Support\Arr;

class UpdateProductVersion
{
    public function __construct(
        protected SaveFile $saveFile
    ) {
    }

    public function handle(Product $product, ProductVersion $productVersion, array $data): void
    {
        $productVersion->update(Arr::except($data, 'version_id'));

        if (Arr::has($data, 'main_file')) {
            $productVersion->main_file = $this->saveFile->handle($data['main_file'], $product, 'main');
        }

        if (Arr::has($data, 'sql_file')) {
            $productVersion->sql_file = $this->saveFile->handle($data['sql_file'], $product, 'sql');
        }

        $productVersion->save();
    }
}
