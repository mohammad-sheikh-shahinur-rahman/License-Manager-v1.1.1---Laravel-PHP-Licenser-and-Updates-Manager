@extends(Theme::getThemeNamespace('layouts.auth'))

@section('content')
    <h2 class="h3 text-center mb-4">
        {{ trans('plugins/license-manager::customer.auth.login') }}
    </h2>

    {!! $form->renderForm() !!}

    @if (is_plugin_active('social-login') && \Botble\SocialLogin\Facades\SocialService::hasAnyProviderEnable())
        <x-core::alert
            type="info"
            icon="ti ti-info-circle"
            class="mt-3"
        >
            <span>{{ trans('plugins/license-manager::customer.auth.social_login_hint') }}</span>
        </x-core::alert>
    @endif
@endsection
