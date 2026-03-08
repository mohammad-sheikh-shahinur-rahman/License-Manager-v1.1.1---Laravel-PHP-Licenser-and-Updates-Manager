<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ trans('plugins/license-manager::license-manager.emails.license_detailm_subject', ['product_reference_id' => $productName]) }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h2 style="color: #4f46e5;">{{ trans('plugins/license-manager::license-manager.emails.license_detailm_title') }}</h2>

    <p>{{ trans('plugins/license-manager::license-manager.emails.license_detailm_greeting', ['customer_id' => $license->customer_id ?? 'Customer']) }}</p>

    <p>{{ trans('plugins/license-manager::license-manager.emails.license_detailm_body', ['product_reference_id' => $productName]) }}</p>

    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #4f46e5;">
        <h3 style="margin-top: 0; color: #4f46e5;">{{ trans('plugins/license-manager::license-manager.emails.license_details') }}</h3>
        <p style="margin: 8px 0;"><strong>{{ trans('plugins/license-manager::license-manager.emails.license_code') }}:</strong></p>
        <p style="font-family: monospace; font-size: 16px; background: #e5e7eb; padding: 10px; border-radius: 4px; word-break: break-all;">{{ $license->license_code }}</p>
        <p style="margin: 8px 0;"><strong>{{ trans('plugins/license-manager::license-manager.emails.product') }}:</strong> {{ $productName }}</p>
        @if($license->license_type)
            <p style="margin: 8px 0;"><strong>{{ trans('plugins/license-manager::license-manager.emails.license_type') }}:</strong> {{ $license->license_type }}</p>
        @endif
        @if($license->parallel_uses)
            <p style="margin: 8px 0;"><strong>{{ trans('plugins/license-manager::license-manager.emails.activations_allowed') }}:</strong> {{ $license->parallel_uses }}</p>
        @endif
        @if($license->expires_at)
            <p style="margin: 8px 0;"><strong>{{ trans('plugins/license-manager::license-manager.emails.expiry_date') }}:</strong> {{ $license->expires_at->format('Y-m-d') }}</p>
        @endif
    </div>

    <p>{{ trans('plugins/license-manager::license-manager.emails.license_detailm_keep_safe') }}</p>

    <p style="margin-top: 30px; color: #666; font-size: 12px;">
        {{ trans('plugins/license-manager::license-manager.emails.footer') }}
    </p>
</body>
</html>
