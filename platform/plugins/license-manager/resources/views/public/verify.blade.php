<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ BaseHelper::siteLanguageDirection() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ trans('plugins/license-manager::license-manager.public_verify.title') }}</title>
    @if ($favicon = theme_option('favicon'))
        {{ Html::favicon(RvMedia::getImageUrl($favicon), ['type' => theme_option('favicon_type', 'image/x-icon')]) }}
    @endif
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --success: #10b981;
            --success-bg: #ecfdf5;
            --warning: #d97706;
            --warning-bg: #fffbeb;
            --danger: #ef4444;
            --danger-bg: #fef2f2;
            --text: #1e293b;
            --text-muted: #64748b;
            --bg: #f1f5f9;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.12);
            max-width: 420px;
            width: 100%;
            padding: 36px 32px;
            animation: fadeIn 0.4s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h1 { color: var(--text); font-size: 22px; font-weight: 700; text-align: center; margin-bottom: 4px; }
        .subtitle { color: var(--text-muted); font-size: 14px; text-align: center; margin-bottom: 28px; }

        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; color: var(--text); font-weight: 500; font-size: 14px; margin-bottom: 8px; }
        .form-group input { width: 100%; padding: 12px 16px; border: 1.5px solid #e2e8f0; border-radius: 10px; font-size: 15px; color: var(--text); transition: border-color 0.2s, box-shadow 0.2s; background: #f8fafc; }
        .form-group input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); background: #fff; }

        .btn-verify { width: 100%; padding: 13px; background: var(--primary); color: #fff; border: none; border-radius: 10px; font-size: 15px; font-weight: 600; cursor: pointer; transition: background 0.2s, transform 0.1s; }
        .btn-verify:hover { background: var(--primary-dark); }
        .btn-verify:active { transform: scale(0.98); }

        .error-message { color: var(--danger); font-size: 13px; margin-top: 6px; }

        /* Result section */
        .result { margin-top: 24px; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; }

        .result-banner {
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 15px;
            font-weight: 700;
            color: #fff;
        }

        .result-banner svg { width: 20px; height: 20px; flex-shrink: 0; }

        .result-banner.valid { background: linear-gradient(135deg, #6366f1, var(--primary-dark)); }
        .result-banner.expired { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .result-banner.invalid { background: linear-gradient(135deg, #ef4444, #dc2626); }

        .result-body { padding: 16px 20px; }

        .result-details { font-size: 14px; color: var(--text-muted); line-height: 1.7; }
        .result-details strong { color: var(--text); }

        .result-status {
            padding: 10px 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 600;
        }

        .result-status svg { width: 16px; height: 16px; }
        .result-status.valid { background: var(--success-bg); color: var(--success); }
        .result-status.expired { background: var(--warning-bg); color: var(--warning); }
        .result-status.invalid { background: var(--danger-bg); color: var(--danger); }

        @media (max-width: 440px) {
            .card { padding: 28px 20px; }
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>{{ trans('plugins/license-manager::license-manager.public_verify.title') }}</h1>
        <p class="subtitle">{{ trans('plugins/license-manager::license-manager.public_verify.subtitle') }}</p>

        <form method="POST" action="{{ route('lm.public.verify') }}">
            @csrf
            <div class="form-group">
                <label for="license_code">{{ trans('plugins/license-manager::license-manager.public_verify.license_code') }}</label>
                <input type="text" id="license_code" name="license_code" value="{{ old('license_code', request('license_code')) }}" placeholder="{{ trans('plugins/license-manager::license-manager.public_verify.license_code_placeholder') }}" required>
                @error('license_code')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="btn-verify">{{ trans('plugins/license-manager::license-manager.public_verify.verify_button') }}</button>
        </form>

        @isset($verified)
            @if($verified)
                <div class="result">
                    <div class="result-banner {{ $isExpired ? 'expired' : 'valid' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>
                        {{ trans('plugins/license-manager::license-manager.public_verify.verified_license') }}
                    </div>
                    <div class="result-body">
                        <div class="result-details">
                            <strong>{{ trans('plugins/license-manager::license-manager.public_verify.product') }}:</strong> {{ $productName }}<br>
                            @if($licenseType)
                                <strong>{{ trans('plugins/license-manager::license-manager.public_verify.license_type') }}:</strong> {{ ucfirst($licenseType) }}<br>
                            @endif
                            @if($expiryDate)
                                <strong>{{ trans('plugins/license-manager::license-manager.public_verify.expiry_date') }}:</strong> {{ $expiryDate }}<br>
                            @endif
                            <strong>{{ trans('plugins/license-manager::license-manager.public_verify.activations') }}:</strong> {{ $activeCount }}/{{ $parallelUses ?? '∞' }}
                        </div>
                    </div>
                    <div class="result-status {{ $isExpired ? 'expired' : 'valid' }}">
                        @if($isExpired)
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                            {{ trans('plugins/license-manager::license-manager.public_verify.expired_license') }}
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            {{ trans('plugins/license-manager::license-manager.public_verify.valid_license') }}
                        @endif
                    </div>
                </div>
            @else
                <div class="result">
                    <div class="result-banner invalid">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z" /></svg>
                        {{ trans('plugins/license-manager::license-manager.public_verify.invalid_license_title') }}
                    </div>
                    <div class="result-body">
                        <div class="result-details">{{ $message }}</div>
                    </div>
                    <div class="result-status invalid">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                        {{ $message }}
                    </div>
                </div>
            @endif
        @endisset
    </div>
</body>
</html>
