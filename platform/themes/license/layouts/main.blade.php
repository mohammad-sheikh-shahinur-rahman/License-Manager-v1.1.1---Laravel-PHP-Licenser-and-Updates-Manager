@extends(Theme::getThemeNamespace('layouts.master'))

@section('head')
    <link href="{{ asset('vendor/core/plugins/license-manager/css/dashboard/style.css?v=' . get_cms_version()) }}" rel="stylesheet">

    @if (BaseHelper::isRtlEnabled())
        <link href="{{ asset('vendor/core/core/base/css/core.rtl.css?v=' . get_cms_version()) }}" rel="stylesheet">
        <link href="{{ asset('vendor/core/plugins/license-manager/css/dashboard/style-rtl.css?v=' . get_cms_version()) }}" rel="stylesheet">
    @endif
@stop

@section('body')
<header class="header--mobile">
    <div class="header__left">
        <button class="navbar-toggler">
            <span class="navbar-toggler-icon"></span>
        </button>
    </div>
    <div class="header__center">
        <a class="ps-logo" href="{{ route('lm.customer.dashboard') }}">
            @if ($logo = theme_option('logo') ?: setting('admin_logo') ?: config('core.base.general.logo'))
                <img
                    src="{{ RvMedia::getImageUrl($logo) }}"
                    style="max-height: 40px;"
                    alt="{{ Theme::getSiteTitle() }}"
                >
            @endif
        </a>
    </div>
    <div class="header__right">
        <a href="{{ route('lm.customer.auth.logout') }}">
            <x-core::icon name="ti ti-logout" />
        </a>
    </div>
</header>
<aside class="ps-drawer--mobile">
    <div class="ps-drawer__header py-3">
        <h4 class="fs-3 mb-0">{{ Theme::getSiteTitle() }}</h4>
        <button class="ps-drawer__close">
            <x-core::icon name="ti ti-x" />
        </button>
    </div>
    <div class="ps-drawer__content">
        @include(Theme::getThemeNamespace('layouts.partials.menu'))
    </div>
</aside>

<div class="ps-site-overlay"></div>

<main class="ps-main">
    <div class="ps-main__sidebar">
        <div class="ps-sidebar">
            <div class="ps-sidebar__top">
                @if ($user = auth('lm_customer')->user())
                <div class="ps-block--user-wellcome">
                    <div class="ps-block__left">
                        <img
                            src="{{ $user->avatar_url }}"
                            alt="{{ $user->name }}"
                            class="avatar avatar-lg"
                        />
                    </div>
                    <div class="ps-block__right">
                        <p>{{ __('Hello') }}, {{ $user->name }}</p>
                        <small>{{ __('Joined on :date', ['date' => $user->created_at->translatedFormat('M d, Y')]) }}</small>
                    </div>
                    <div class="ps-block__action">
                        <x-core::form method="post" :url="route('lm.customer.auth.logout')">
                            <x-core::button :icon-only="true" size="sm" type="submit" icon="ti ti-logout"/>
                        </x-core::form>
                    </div>
                </div>
                @endif
            </div>
            <div class="ps-sidebar__content">
                <div class="ps-sidebar__center">
                    @include(Theme::getThemeNamespace('layouts.partials.menu'))
                </div>
                <div class="ps-sidebar__footer">
                    <div class="ps-copyright">
                        @php $logo = theme_option('logo') ?: setting('admin_logo'); @endphp
                        <img
                            src="{{ $logo ? RvMedia::getImageUrl($logo) : config('core.base.general.logo') }}"
                            alt="{{ Theme::getSiteTitle() }}"
                            style="max-height: 40px;"
                        >
                        @if ($copyright = Theme::getSiteCopyright())
                            <p>{!! $copyright !!}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div
        class="ps-main__wrapper"
        id="vendor-dashboard"
    >
        <header class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="fs-1">{{ PageTitle::getTitle(false) }}</h3>
        </header>

        <div id="app">
            @hasSection('content')
                @yield('content')
            @else
                {!! Theme::content() !!}
            @endif
        </div>
    </div>
</main>
@stop

@push('footer')
    <script src="{{ asset('vendor/core/plugins/license-manager/js/dashboard/script.js?v=' . get_cms_version()) }}"></script>
@endpush
