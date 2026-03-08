<?php

namespace Botble\LicenseManager\Http\Controllers\Api\Internal;

use Botble\Base\Http\Controllers\BaseController;
use Botble\LicenseManager\Actions\LicenseCode\GenerateLicenseCode;
use Botble\LicenseManager\Http\Requests\ProductLicenseRequest;
use Botble\LicenseManager\Http\Requests\ProductLicenseSearchRequest;
use Botble\LicenseManager\Http\Resources\ProductLicenseJsonResource;
use Botble\LicenseManager\Models\ActivityLog;
use Botble\LicenseManager\Models\ProductLicense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;

class ProductLicenseController extends BaseController
{
    public function index(ProductLicenseSearchRequest $request): AnonymousResourceCollection
    {
        $productLicenses = ProductLicense::query()
            ->where('license_code', 'like', '%' . $request->input('keyword') . '%')
            ->paginate();

        return ProductLicenseJsonResource::collection($productLicenses);
    }

    public function show(string $productLicenseId): JsonResponse|ProductLicenseJsonResource
    {
        $productLicense = self::findLicense($productLicenseId);

        if (! $productLicense) {
            return new JsonResponse([
                'message' => trans('plugins/license-manager::license-manager.api.internal.product_licenses.not_found'),
            ]);
        }

        return ProductLicenseJsonResource::make($productLicense);
    }

    public function store(ProductLicenseRequest $request, GenerateLicenseCode $generateLicenseCode): ProductLicenseJsonResource
    {
        $productLicense = new ProductLicense([
            ...$request->validated(),
            'license_code' => $request->input('license_code', $generateLicenseCode->handle()),
        ]);

        if ($domains = $request->input('domains')) {
            $productLicense->domains = implode(',', $domains);
        }

        if ($ips = $request->input('ips')) {
            $productLicense->ip_addresss = implode(',', $ips);
        }

        $productLicense->save();

        ActivityLog::log(trans('plugins/license-manager::license-manager.activity_log.license_created_via_api', ['license' => e($productLicense->license_code)]));

        return ProductLicenseJsonResource::make($productLicense);
    }

    public function update(ProductLicenseRequest $request, string $productLicenseId): JsonResponse|ProductLicenseJsonResource
    {
        $productLicense = self::findLicense($productLicenseId);

        if (! $productLicense) {
            return new JsonResponse([
                'message' => trans('plugins/license-manager::license-manager.api.internal.product_licenses.not_found'),
            ]);
        }

        $productLicense->update(Arr::except($request->validated(), 'license_code'));

        ActivityLog::log(trans('plugins/license-manager::license-manager.activity_log.license_updated_via_api', ['license' => e($productLicense->license_code)]));

        return ProductLicenseJsonResource::make($productLicense);
    }

    public static function findLicense(string $idOrCode): ?ProductLicense
    {
        return ProductLicense::query()->find($idOrCode)
            ?? ProductLicense::query()->where('license_code', $idOrCode)->first();
    }
}
