<?php

namespace Botble\LicenseManager\Actions\ProductVersions;

use Botble\LicenseManager\Models\Product;
use Botble\LicenseManager\Models\ProductVersion;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StoreProductVersion
{
    public function __construct(
        protected SaveFile $saveFile
    ) {
    }

    public function handle(Product $product, array $data): ProductVersion
    {
        $version = Arr::get($data, 'version');

        $product->load('versions');

        $versionExists = $product->versions->isNotEmpty();

        /**
         * @var ProductVersion $latestVersion
         */
        $latestVersion = $product->versions->sortByDesc('id')->first();

        if (empty(trim($data['changelog'])) && $latestVersion === null) {
            throw ValidationException::withMessages([
                'changelog' => trans('plugins/license-manager::license-manager.product_version.validation.changelog_required_for_first_version'),
            ]);
        }

        if (
            ! $versionExists
            && $latestVersion !== null
            && version_compare($version, $latestVersion['version'], '<')
        ) {
            throw ValidationException::withMessages([
                'version' => trans('plugins/license-manager::license-manager.product_version.validation.version_lower_than_latest'),
            ]);
        }

        if (! Arr::has($data, 'main_file')) {
            throw ValidationException::withMessages([
                'main_file' => trans('plugins/license-manager::license-manager.product_version.validation.main_file_required'),
            ]);
        }

        /** @var ProductVersion $productVersion */
        $productVersion = ProductVersion::query()->firstOrNew(['product_reference_id' => $product->reference_id, 'version' => $version]);

        // Delete old version files BEFORE uploading new ones to avoid filename collisions
        if ($versionExists) {
            $latestVersion->deleteVersionFiles();
        }

        $uniqueId = Arr::get($data, 'version_id', hash('sha1', (string) Str::ulid()));

        $productVersion->fill([
            'product_reference_id' => $product->reference_id,
            'version_id' => $uniqueId,
            ...$data,
        ]);

        $productVersion->main_file = $this->saveFile->handle($data['main_file'], $product, 'main');

        if (Arr::has($data, 'sql_file')) {
            $productVersion->sql_file = $this->saveFile->handle($data['sql_file'], $product, 'sql');
        }

        $productVersion->save();

        if (Arr::get($data, 'delete_old_versions') == 'yes') {
            $product->versions()
                ->whereNot('version', $productVersion->version)
                ->each(fn (ProductVersion $version) => $version->delete());
        }

        return $productVersion;
    }
}
