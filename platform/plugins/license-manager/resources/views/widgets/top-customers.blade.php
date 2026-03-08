<div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
    <table class="table table-vcenter card-table table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ trans('plugins/license-manager::license-manager.dashboard_widgets.customer') }}</th>
                <th class="text-end">{{ trans('plugins/license-manager::license-manager.dashboard_widgets.licenses_count') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($customers as $index => $customer)
                <tr>
                    <td class="text-muted">{{ $index + 1 }}</td>
                    <td>
                        @if($customer['customer_id'])
                            <a href="{{ route('lm.customers.edit', $customer['customer_id']) }}">
                                {{ $customer['name'] }}
                            </a>
                            @if($customer['email'])
                                <div class="text-muted small">{{ $customer['email'] }}</div>
                            @endif
                        @else
                            {{ $customer['name'] }}
                        @endif
                    </td>
                    <td class="text-end">
                        <span class="badge bg-blue text-blue-fg">{{ $customer['licenses_count'] }}</span>
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
