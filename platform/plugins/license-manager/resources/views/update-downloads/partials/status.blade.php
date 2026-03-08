@if ($item->is_valid)
    <x-core::badge color="success" :label="trans('plugins/license-manager::license-manager.activations.invalid')" />
@else
    <x-core::badge color="danger" :label="trans('plugins/license-manager::license-manager.activations.invalid')" />
@endif
