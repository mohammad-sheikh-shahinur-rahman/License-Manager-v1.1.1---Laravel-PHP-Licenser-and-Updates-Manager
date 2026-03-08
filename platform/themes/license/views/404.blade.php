@php
    SeoHelper::setTitle(__('404 - Not found'));
    Theme::fireEventGlobalAssets();
@endphp

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ SeoHelper::getTitle() }}</title>
    {!! Theme::header() !!}
    <style>
        .error-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f5f7fb 0%, #e8ecf4 100%);
        }
        .error-card {
            text-align: center;
            max-width: 480px;
            padding: 3rem 2rem;
        }
        .error-code {
            font-size: 8rem;
            font-weight: 800;
            line-height: 1;
            background: linear-gradient(135deg, var(--tblr-primary) 0%, #6e5dc6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }
        .error-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.75rem;
        }
        .error-description {
            color: #64748b;
            font-size: 1rem;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        .error-actions .btn {
            padding: 0.625rem 1.5rem;
            font-weight: 500;
            border-radius: 8px;
        }
        .error-divider {
            width: 48px;
            height: 3px;
            background: linear-gradient(135deg, var(--tblr-primary) 0%, #6e5dc6 100%);
            border-radius: 2px;
            margin: 1.25rem auto;
        }
    </style>
</head>
<body>
    <div class="error-page">
        <div class="error-card">
            <div class="error-code">404</div>
            <div class="error-divider"></div>
            <h1 class="error-title">{{ __('Page Not Found') }}</h1>
            <p class="error-description">{{ __('Sorry, the page you are looking for could not be found or has been moved.') }}</p>
            <div class="error-actions">
                <a href="{{ BaseHelper::getHomepageUrl() }}" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-arrow-left" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0"/><path d="M5 12l6 6"/><path d="M5 12l6 -6"/></svg>
                    {{ __('Back to Home') }}
                </a>
            </div>
        </div>
    </div>
    {!! Theme::footer() !!}
</body>
</html>
