@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <x-core::card>
        <x-core::card.body>
            <x-core::datagrid class="mb-4">
                <x-core::datagrid.item>
                    <x-slot:title>
                        {{ trans('plugins/license-manager::license-manager.product.unique_id') }}
                    </x-slot:title>

                    <span class="font-monospace">{{ $product->reference_id }}</span>

                    <x-core::copy :copyableState="$product->reference_id" />
                </x-core::datagrid.item>

                <x-core::datagrid.item>
                    <x-slot:title>
                        {{ trans('plugins/license-manager::license-manager.product.product_name') }}
                    </x-slot:title>
                    {{ $product->name }}
                </x-core::datagrid.item>

                    @if (setting('lm_enable_envato_integration') || setting('envato_personal_token'))
                        <x-core::datagrid.item>
                            <x-slot:title>
                                {{ trans('plugins/license-manager::license-manager.product.envato_id') }}
                            </x-slot:title>

                            @if ($product->envato_id)
                                <a href="{{ $product->url }}" target="_blank">
                                    <span class="font-monospace">{{ $product->envato_id }}</span>
                                    <x-core::icon name="ti ti-external-link" />
                                </a>
                            @else
                                <span>&mdash;</span>
                            @endif
                        </x-core::datagrid.item>
                    @endif

            </x-core::datagrid>

            <x-core::datagrid>
                @if ($product->description)
                    <x-core::datagrid.item>
                        <x-slot:title>
                            {{ trans('plugins/license-manager::license-manager.product.product_details') }}
                        </x-slot:title>

                        {{ $product->description }}
                    </x-core::datagrid.item>
                @endif

                <x-core::datagrid.item>
                    <x-slot:title>
                        {{ trans('plugins/license-manager::license-manager.product.license_update') }}
                    </x-slot:title>

                    @include('core/table::includes.columns.yes-no', ['value' => $product->license_update])
                </x-core::datagrid.item>

                <x-core::datagrid.item>
                    <x-slot:title>
                        {{ trans('plugins/license-manager::license-manager.product.serve_latest_updates') }}
                    </x-slot:title>
                    @include('core/table::includes.columns.yes-no', ['value' => $product->serve_latest_updates])
                </x-core::datagrid.item>

                <x-core::datagrid.item>
                    <x-slot:title>
                        {{ trans('core/base::forms.status') }}
                    </x-slot:title>
                    @include('plugins/license-manager::products.partials.status')
                </x-core::datagrid.item>
            </x-core::datagrid>
        </x-core::card.body>
    </x-core::card>

    <x-core::card class="mt-3">
        <x-core::card.header>
            <x-core::card.title>
                {{ trans('plugins/license-manager::license-manager.product.version.manage_versions', ['name' => $product->name]) }}
            </x-core::card.title>

            <div class="card-actions">
                <a href="{{ route('lm.bulk-generate.index', ['product_reference_id' => $product->reference_id]) }}" class="btn btn-primary">
                    <x-core::icon name="ti ti-plus" />
                    {{ trans('plugins/license-manager::license-manager.product.bulk_generate_licenses') }}
                </a>
            </div>
        </x-core::card.header>
        {!! $productVersionTable->render('core/table::base-table') !!}
    </x-core::card>
@endsection
