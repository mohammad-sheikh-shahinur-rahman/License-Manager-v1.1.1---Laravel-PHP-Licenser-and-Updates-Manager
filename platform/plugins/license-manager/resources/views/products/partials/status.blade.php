@if ($product->is_active)
    <x-core::badge color="success" :label="trans('plugins/license-manager::license-manager.activations.active')" />
@else
    <x-core::badge color="danger" :label="trans('plugins/license-manager::license-manager.activations.inactive')" />
@endif
