<x-core::badge :color="$is_active ? 'success' : 'danger'">
    {{ $is_active
        ? trans('plugins/license-manager::license-manager.licenses.normal')
        : trans('plugins/license-manager::license-manager.licenses.block')
     }}
</x-core::badge>
