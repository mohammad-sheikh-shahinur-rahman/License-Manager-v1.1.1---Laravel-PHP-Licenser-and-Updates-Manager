<div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
    <table class="table table-vcenter card-table table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ trans('plugins/license-manager::license-manager.dashboard_widgets.product') }}</th>
                <th class="text-end">{{ trans('plugins/license-manager::license-manager.dashboard_widgets.licenses_count') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $index => $product)
                <tr>
                    <td class="text-muted">{{ $index + 1 }}</td>
                    <td>
                        @if($product['product_id'])
                            <a href="{{ route('lm.products.edit', $product['product_id']) }}">
                                {{ $product['name'] }}
                            </a>
                        @else
                            {{ $product['name'] }}
                        @endif
                    </td>
                    <td class="text-end">
                        <span class="badge bg-green text-green-fg">{{ $product['licenses_count'] }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center text-muted py-4">
                        {{ trans('plugins/license-manager::license-manager.dashboard_widgets.no_data') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
