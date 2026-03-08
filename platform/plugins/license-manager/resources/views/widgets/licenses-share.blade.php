<div id="licenses-share-chart" style="height: 300px; width: 100%;"></div>

<script>
    window.licensesShareData = {
        valid: {{ $stats['valid'] }},
        invalid: {{ $stats['invalid'] }},
        blocked: {{ $stats['blocked'] }},
        labels: {
            valid: '{{ trans('plugins/license-manager::license-manager.licenses.statuses.valid') }}',
            invalid: '{{ trans('plugins/license-manager::license-manager.licenses.statuses.invalid') }}',
            blocked: '{{ trans('plugins/license-manager::license-manager.licenses.statuses.blocked') }}'
        },
        noDataText: '{{ trans('plugins/license-manager::license-manager.dashboard_widgets.no_data') }}'
    };
</script>
