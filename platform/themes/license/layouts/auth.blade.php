@extends(Theme::getThemeNamespace('layouts.master'))

@section('body')
    @php
        $backgroundUrl = asset('vendor/core/plugins/license-manager/images/bg-guest.jpg?v=' . get_cms_version());
    @endphp
    <div class="row g-0 flex-fill">
        <div
            class="col-12 col-lg-6 col-xl-4 border-top-wide border-primary d-flex flex-column justify-content-center">
            <div class="container container-tight my-5 px-lg-5">
                <div class="text-center mb-4">
                    @php
                        $logo = theme_option('logo') ?: setting('admin_logo')
                    @endphp
                    <a
                        href="{{ route('public.index') }}"
                        class="navbar-brand navbar-brand-autodark"
                    >
                        <img
                            src="{{ $logo ? RvMedia::getImageUrl($logo) : url(config('core.base.general.logo')) }}"
                            style="max-height: 40px;"
                            alt="{{ Theme::getSiteTitle() }}"
                        >
                    </a>
                </div>
                @yield('content')
            </div>
        </div>
        <div class="position-relative col-12 col-lg-6 col-xl-8 d-none d-lg-block">
            <div
                class="bg-cover h-100 min-vh-100"
                style="background-image: url('{{ $backgroundUrl }}');"
            ></div>

            <div class="end-0 bottom-0 position-absolute">
                <div class="text-white me-5 mb-4">
                    <h1 class="mb-1">{{ Theme::getSiteTitle() }}</h1>

                    @if ($copyright = Theme::getSiteCopyright())
                        <p>{!! $copyright !!}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
