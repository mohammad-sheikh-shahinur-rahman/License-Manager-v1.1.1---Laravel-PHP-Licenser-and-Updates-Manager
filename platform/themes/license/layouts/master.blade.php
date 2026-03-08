<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0"
    >
    <meta
        http-equiv="X-UA-Compatible"
        content="ie=edge"
    >
    <title>{{ PageTitle::getTitle() ?: Theme::getSiteTitle() }}</title>
    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    @if (($favicon = theme_option('favicon') ?: setting('admin_favicon')) || config('core.base.general.favicon'))
        <link
            href="{{ $favicon ? RvMedia::getImageUrl($favicon) : url(config('core.base.general.favicon')) }}"
            rel="icon shortcut"
        >
        <meta
            property="og:image"
            content="{{ $favicon }}"
        >
    @endif

    @php
        $title = Theme::getSiteTitle();
    @endphp

    <meta
        name="description"
        content="{{ $copyright = strip_tags(trans('core/base::layouts.copyright', ['year' => Carbon\Carbon::now()->format('Y'), 'company' => $title, 'version' => get_cms_version()])) }}"
    >
    <meta
        property="og:description"
        content="{{ $copyright }}"
    >

    @include(Theme::getThemeNamespace('layouts.partials.header'))

    @yield('head')

    <script>
        window.siteUrl = "{{ url('') }}";
        window.siteEditorLocale = "{{ apply_filters('cms_site_editor_locale', App::getLocale()) }}";
    </script>

    @stack('header')

    {!! apply_filters(BASE_FILTER_HEAD_LAYOUT_TEMPLATE, null) !!}
</head>

<body
    class="@yield('body-class', $bodyClass ?? 'page-sidebar-closed-hide-logo page-content-white page-container-bg-solid') {{ session()->get('sidebar-menu-toggle') ? 'page-sidebar-closed' : '' }}"
    style="@yield('body-style', $bodyStyle ?? null)"
    @if (BaseHelper::adminLanguageDirection() === 'rtl') dir="rtl" @endif
>

@yield('headerLayout')

{!! apply_filters(BASE_FILTER_HEADER_LAYOUT_TEMPLATE, null) !!}

<div id="app">
    @yield('body')
</div>

{!! Assets::renderFooter() !!}

<div id="stack-footer">
    @stack('footer')
</div>

{!! apply_filters(BASE_FILTER_FOOTER_LAYOUT_TEMPLATE, null) !!}

<script type="text/javascript">
    var BotbleVariables = BotbleVariables || {};

    BotbleVariables.languages = {
        tables: {{ Js::from(trans('core/base::tables')) }},
        notices_msg: {{ Js::from(trans('core/base::notices')) }},
    };
</script>

@if (Session::has('success_msg') || Session::has('error_msg') || (isset($errors) && $errors->any()) || isset($error_msg))
    <script type="text/javascript">
        $(function() {
            @if (Session::has('success_msg'))
            Botble.showSuccess('{{ session('success_msg') }}');
            @endif
            @if (Session::has('error_msg'))
            Botble.showError('{{ session('error_msg') }}');
            @endif
            @if (isset($error_msg))
            Botble.showError('{{ $error_msg }}');
            @endif
            @if (isset($errors))
            @foreach ($errors->all() as $error)
            Botble.showError('{{ $error }}');
            @endforeach
            @endif
        })
    </script>
@endif
</body>
</html>
