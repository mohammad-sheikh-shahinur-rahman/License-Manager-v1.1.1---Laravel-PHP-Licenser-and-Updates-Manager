@extends(Theme::getThemeNamespace('layouts.main'))

@section('content')
    <x-core::stat-widget class="mb-3 row-cols-1 row-cols-sm-2 row-cols-md-3">
        <x-core::stat-widget.item
            :label="trans('plugins/license-manager::license-manager.customers.product_licenses')"
            :value="$licensesCount"
            icon="ti ti-license"
            color="primary"
        />

        <x-core::stat-widget.item
            :label="trans('plugins/license-manager::license-manager.customers.active_activations')"
            :value="$activeActivationsCount"
            icon="ti ti-checklist"
            color="success"
        />

        <x-core::stat-widget.item
            :label="trans('plugins/license-manager::license-manager.customers.inactive_activations')"
            :value="$inactiveActivationsCount"
            icon="ti ti-checklist"
            color="warning"
        />
    </x-core::stat-widget>

    {!! apply_filters('envato_products', null) !!}

    @if($activityLogs->isNotEmpty())
        <x-core::card class="mt-4">
            <x-core::card.header>
                <x-core::card.title>
                    {{ trans('plugins/license-manager::license-manager.customers.recent_activity') }}
                </x-core::card.title>
            </x-core::card.header>
            <x-core::card.body class="p-0">
                <div class="list-group list-group-flush">
                    @foreach($activityLogs as $log)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    @switch($log->type)
                                        @case('license_activated')
                                            <i class="ti ti-check text-success me-2"></i>
                                            @break
                                        @case('license_deactivated')
                                            <i class="ti ti-x text-warning me-2"></i>
                                            @break
                                        @case('license_verified')
                                            <i class="ti ti-shield-check text-info me-2"></i>
                                            @break
                                        @case('update_downloaded')
                                            <i class="ti ti-download text-primary me-2"></i>
                                            @break
                                        @case('login')
                                            <i class="ti ti-login text-secondary me-2"></i>
                                            @break
                                        @default
                                            <i class="ti ti-activity text-muted me-2"></i>
                                    @endswitch
                                    {!! \Botble\Base\Facades\BaseHelper::clean($log->message) !!}
                                </div>
                                <small class="text-muted">
                                    {{ $log->created_at->diffForHumans() }}
                                </small>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-core::card.body>
        </x-core::card>
    @endif
@stop
