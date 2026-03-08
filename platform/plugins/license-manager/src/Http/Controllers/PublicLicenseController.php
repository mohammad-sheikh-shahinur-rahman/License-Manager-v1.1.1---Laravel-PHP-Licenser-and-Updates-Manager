<?php

namespace Botble\LicenseManager\Http\Controllers;

use Botble\LicenseManager\Models\ProductLicense;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicLicenseController extends Controller
{
    public function showVerifyForm(): View
    {
        return view('plugins/license-manager::public.verify');
    }

    public function verify(Request $request): View
    {
        $request->validate([
            'license_code' => ['required', 'string', 'max:255'],
        ]);

        $licenseCode = $request->input('license_code');

        // Case-insensitive search if configured
        if (setting('lm_license_code_case_insensitive') !== 'mix') {
            $licenseCode = strtoupper($licenseCode);
        }

        $license = ProductLicense::query()
            ->where(function ($query) use ($licenseCode): void {
                // Case-insensitive comparison
                $query->whereRaw('LOWER(license_code) = ?', [Str::lower($licenseCode)]);
            })
            ->with('product')
            ->first();

        if (! $license) {
            return view('plugins/license-manager::public.verify', [
                'verified' => false,
                'message' => trans('plugins/license-manager::license-manager.public_verify.invalid_license'),
            ]);
        }

        // Check if license is blocked
        if (! $license->is_valid) {
            return view('plugins/license-manager::public.verify', [
                'verified' => false,
                'message' => trans('plugins/license-manager::license-manager.public_verify.license_blocked'),
            ]);
        }

        // Check expiry
        $isExpired = false;
        if ($license->expires_at && Carbon::now()->startOfDay()->gt($license->expires_at)) {
            $isExpired = true;
        }

        $productName = $license->product?->name ?? trans('plugins/license-manager::license-manager.public_verify.unknown_product');

        $activeCount = $license->activations()->where('is_active', true)->count();

        return view('plugins/license-manager::public.verify', [
            'verified' => true,
            'isExpired' => $isExpired,
            'productName' => $productName,
            'licenseType' => $license->type,
            'expiryDate' => $license->expires_at?->format('Y-m-d'),
            'parallelUses' => $license->parallel_uses,
            'activeCount' => $activeCount,
            'message' => $isExpired
                ? trans('plugins/license-manager::license-manager.public_verify.license_expired')
                : trans('plugins/license-manager::license-manager.public_verify.license_valid'),
        ]);
    }
}
