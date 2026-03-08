@if ($item->is_valid)
    @if ($item->is_active)
        <x-core::badge color="success">
            {{ trans('plugins/license-manager::license-manager.activations.active') }}
        </x-core::badge>
    @else
        <x-core::badge color="primary">
            {{ trans('plugins/license-manager::license-manager.activations.inactive') }}
        </x-core::badge>
    @endif
@else
    <x-core::badge color="danger">
        {{ trans('plugins/license-manager::license-manager.activations.invalid') }}
    </x-core::badge>
@endif

