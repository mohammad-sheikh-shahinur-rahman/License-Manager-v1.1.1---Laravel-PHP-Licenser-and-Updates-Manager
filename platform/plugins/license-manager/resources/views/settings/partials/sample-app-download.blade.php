<x-core::card class="mb-3">
    <x-core::card.header>
        <x-core::card.title>
            {{ trans('plugins/license-manager::license-manager.sample_app.title') }}
        </x-core::card.title>
    </x-core::card.header>
    <x-core::card.body>
        <p class="text-muted mb-3">
            {{ trans('plugins/license-manager::license-manager.sample_app.description') }}
        </p>

        <div class="row">
            <div class="col-md-6 mb-3">
                <h4 class="mb-2">{{ trans('plugins/license-manager::license-manager.sample_app.external_title') }}</h4>
                <p class="text-muted small mb-2">
                    {{ trans('plugins/license-manager::license-manager.sample_app.external_description') }}
                </p>
                <ul class="small mb-3">
                    <li>{{ trans('plugins/license-manager::license-manager.sample_app.feature_1') }}</li>
                    <li>{{ trans('plugins/license-manager::license-manager.sample_app.feature_2') }}</li>
                    <li>{{ trans('plugins/license-manager::license-manager.sample_app.feature_3') }}</li>
                    <li>{{ trans('plugins/license-manager::license-manager.sample_app.feature_4') }}</li>
                </ul>
                <a
                    href="{{ route('lm.api.sample-app.download', ['type' => 'external']) }}"
                    class="btn btn-primary"
                >
                    <x-core::icon name="ti ti-download" class="me-1" />
                    {{ trans('plugins/license-manager::license-manager.sample_app.download_external') }}
                </a>
            </div>

            <div class="col-md-6 mb-3">
                <h4 class="mb-2">{{ trans('plugins/license-manager::license-manager.sample_app.internal_title') }}</h4>
                <p class="text-muted small mb-2">
                    {{ trans('plugins/license-manager::license-manager.sample_app.internal_description') }}
                </p>
                <ul class="small mb-3">
                    <li>{{ trans('plugins/license-manager::license-manager.sample_app.internal_feature_1') }}</li>
                    <li>{{ trans('plugins/license-manager::license-manager.sample_app.internal_feature_2') }}</li>
                    <li>{{ trans('plugins/license-manager::license-manager.sample_app.internal_feature_3') }}</li>
                    <li>{{ trans('plugins/license-manager::license-manager.sample_app.internal_feature_4') }}</li>
                </ul>
                <a
                    href="{{ route('lm.api.sample-app.download', ['type' => 'internal']) }}"
                    class="btn btn-outline-primary"
                >
                    <x-core::icon name="ti ti-download" class="me-1" />
                    {{ trans('plugins/license-manager::license-manager.sample_app.download_internal') }}
                </a>
            </div>
        </div>
    </x-core::card.body>
</x-core::card>
