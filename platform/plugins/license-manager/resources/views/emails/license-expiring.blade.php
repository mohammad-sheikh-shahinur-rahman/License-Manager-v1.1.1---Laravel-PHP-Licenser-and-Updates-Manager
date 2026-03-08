<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ trans('plugins/license-manager::license-manager.emails.expiring_subject', ['product_reference_id' => $productName, 'days' => $daysUntilExpiry]) }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h2 style="color: #e67e22;">{{ trans('plugins/license-manager::license-manager.emails.expiring_title') }}</h2>

    <p>{{ trans('plugins/license-manager::license-manager.emails.expiring_greeting', ['customer_id' => $license->customer_id ?? 'Customer']) }}</p>

    <p>{{ trans('plugins/license-manager::license-manager.emails.expiring_body', ['product_reference_id' => $productName, 'days' => $daysUntilExpiry]) }}</p>

    <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
        <p style="margin: 5px 0;"><strong>{{ trans('plugins/license-manager::license-manager.emails.license_code') }}:</strong> {{ $license->license_code }}</p>
        <p style="margin: 5px 0;"><strong>{{ trans('plugins/license-manager::license-manager.emails.product') }}:</strong> {{ $productName }}</p>
        <p style="margin: 5px 0;"><strong>{{ trans('plugins/license-manager::license-manager.emails.expiry_date') }}:</strong> {{ $expiryDate }}</p>
    </div>

    <p>{{ trans('plugins/license-manager::license-manager.emails.expiring_action') }}</p>

    <p style="margin-top: 30px; color: #666; font-size: 12px;">
        {{ trans('plugins/license-manager::license-manager.emails.footer') }}
    </p>
</body>
</html>
