<div id="licenses-activations-chart" style="height: 300px; width: 100%;"></div>

<script>
    window.licensesActivationsData = {
        data: @json($chartData),
        labels: {
            licenses: '{{ trans('plugins/license-manager::license-manager.dashboard_widgets.licenses_added') }}',
            activations: '{{ trans('plugins/license-manager::license-manager.dashboard_widgets.valid_activations') }}',
            downloads: '{{ trans('plugins/license-manager::license-manager.dashboard_widgets.updates_downloaded') }}'
        },
        noDataText: '{{ trans('plugins/license-manager::license-manager.dashboard_widgets.no_data') }}'
    };
</script>
