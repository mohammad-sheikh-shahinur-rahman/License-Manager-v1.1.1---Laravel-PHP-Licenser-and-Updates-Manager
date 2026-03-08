@extends('packages/data-synchronize::export')

@section('export_extra_filters_before')
    @php
        $products = \Botble\LicenseManager\Models\Product::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    @endphp

    <div class="row mb-3">
        <div class="col-md-3">
            <x-core::form.select
                name="product_id"
                :label="trans('plugins/license-manager::license-manager.export.product')"
                :options="['' => trans('plugins/license-manager::license-manager.export.all_products')] + $products"
            />
        </div>
        <div class="col-md-3">
            <x-core::form.select
                name="is_valid"
                value=""
                :label="trans('plugins/license-manager::license-manager.export.status')"
                :options="[
                    '' => trans('plugins/license-manager::license-manager.export.all_statuses'),
                    '1' => trans('plugins/license-manager::license-manager.export.valid'),
                    '0' => trans('plugins/license-manager::license-manager.export.invalid'),
                ]"
            />
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-3">
            <label
                for="start_date"
                class="form-label"
            >{{ trans('plugins/license-manager::license-manager.export.start_date') }}</label>

            {!! Form::datePicker('start_date', null, [
                'placeholder' => trans('plugins/license-manager::license-manager.export.start_date'),
            ]) !!}
        </div>
        <div class="col-md-3">
            <label
                for="end_date"
                class="form-label"
            >{{ trans('plugins/license-manager::license-manager.export.end_date') }}</label>

            {!! Form::datePicker('end_date', null, [
                'placeholder' => trans('plugins/license-manager::license-manager.export.end_date'),
            ]) !!}
        </div>
    </div>
@stop
