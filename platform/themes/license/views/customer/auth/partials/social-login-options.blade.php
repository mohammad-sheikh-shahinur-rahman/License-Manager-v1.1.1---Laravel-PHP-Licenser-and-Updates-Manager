@php
    use Botble\SocialLogin\Facades\SocialService;

    $params = ['guard' => 'lm_customer'];
@endphp

@if (SocialService::hasAnyProviderEnable())
    <div class="login-options mb-3">
        <ul class="social-icons social-login-lg list-unstyled mb-0">
            @foreach (SocialService::getProviderKeys() as $item)
                @continue(!SocialService::getProviderEnabled($item))

                {!! apply_filters(
                    'social_login_' . $item . '_render',
                    view('plugins/social-login::social-login-item', [
                        'social' => $item,
                        'url' => route('auth.social', array_merge([$item], $params)),
                    ])->render(),
                    $item,
                ) !!}
            @endforeach
        </ul>
    </div>
@endif
