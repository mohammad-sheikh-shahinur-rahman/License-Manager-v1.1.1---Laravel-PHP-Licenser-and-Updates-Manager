@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <x-core::card>
        <x-core::card.body>
            <x-core::datagrid class="mb-4">
                <x-core::datagrid.item>
                    <x-slot:title>
                        {{ trans('plugins/license-manager::license-manager.product_version.form.version') }}
                    </x-slot:title>
                    <span class="font-monospace">{{ $version->version }}</span>
                </x-core::datagrid.item>

                <x-core::datagrid.item>
                    <x-slot:title>
                        {{ trans('plugins/license-manager::license-manager.product_version.form.release_date') }}
                    </x-slot:title>
                    {{ $version->released_at }}
                </x-core::datagrid.item>

                <x-core::datagrid.item>
                    <x-slot:title>
                        {{ trans('core/base::forms.status') }}
                    </x-slot:title>
                    @if ($version->is_active)
                        {!! BaseHelper::renderBadge(trans('plugins/license-manager::license-manager.product_version.published'), 'success') !!}
                    @else
                        {!! BaseHelper::renderBadge(trans('plugins/license-manager::license-manager.product_version.unpublished'), 'secondary') !!}
                    @endif
                </x-core::datagrid.item>
            </x-core::datagrid>

            @if ($version->summary)
                <x-core::datagrid class="mb-4">
                    <x-core::datagrid.item>
                        <x-slot:title>
                            {{ trans('plugins/license-manager::license-manager.product_version.form.summary') }}
                        </x-slot:title>
                        {{ $version->summary }}
                    </x-core::datagrid.item>
                </x-core::datagrid>
            @endif

            @if ($version->changelog)
                <x-core::datagrid class="mb-4">
                    <x-core::datagrid.item>
                        <x-slot:title>
                            {{ trans('plugins/license-manager::license-manager.product_version.form.changelog') }}
                        </x-slot:title>
                        {!! BaseHelper::clean(nl2br(e($version->changelog))) !!}
                    </x-core::datagrid.item>
                </x-core::datagrid>
            @endif
        </x-core::card.body>
    </x-core::card>

    <x-core::card class="mt-3">
        <x-core::card.header>
            <x-core::card.title>
                {{ trans('plugins/license-manager::license-manager.product_version.download_files') }}
            </x-core::card.title>
        </x-core::card.header>
        <x-core::card.body>
            <div class="d-flex gap-2">
                @if ($version->main_file)
                    <a href="{{ route('lm.products.versions.download-files', [$product, $version]) }}" class="btn btn-info">
                        <x-core::icon name="ti ti-download" />
                        {{ trans('plugins/license-manager::license-manager.product_version.form.main_file') }}
                    </a>
                @endif

                @if ($version->sql_file)
                    <a href="{{ route('lm.products.versions.download-sql', [$product, $version]) }}" class="btn btn-info">
                        <x-core::icon name="ti ti-database" />
                        {{ trans('plugins/license-manager::license-manager.product_version.form.sql_file') }}
                    </a>
                @endif
            </div>

            @if (! $version->main_file && ! $version->sql_file)
                <span class="text-muted">{{ trans('plugins/license-manager::license-manager.product_version.no_files') }}</span>
            @endif
        </x-core::card.body>
    </x-core::card>
@endsection
