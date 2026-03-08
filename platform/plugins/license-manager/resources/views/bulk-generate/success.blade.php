@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <x-core::alert type="success" class="mb-3">
        {{ trans('plugins/license-manager::license-manager.bulk_generate.success_message', ['count' => count($licenseCodes), 'product' => $product['name']]) }}
    </x-core::alert>

    <x-core::card class="mb-3">
        <x-core::card.body>
            <x-core::datagrid>
                <x-core::datagrid.item>
                    <x-slot:title>
                        {{ trans('plugins/license-manager::license-manager.product.product_name') }}
                    </x-slot:title>
                    {{ $product['name'] }}
                </x-core::datagrid.item>

                <x-core::datagrid.item>
                    <x-slot:title>
                        {{ trans('plugins/license-manager::license-manager.product.unique_id') }}
                    </x-slot:title>
                    <span class="font-monospace">{{ $product['reference_id'] }}</span>
                </x-core::datagrid.item>
            </x-core::datagrid>

            <x-core::datagrid class="mt-3">
                <x-core::datagrid.item>
                    <x-slot:title>
                        {{ trans('plugins/license-manager::license-manager.product_license.form.license_type') }}
                    </x-slot:title>
                    {{ $licenseInfo['type'] ?? '—' }}
                </x-core::datagrid.item>

                <x-core::datagrid.item>
                    <x-slot:title>
                        {{ trans('plugins/license-manager::license-manager.product_license.form.uses') }}
                    </x-slot:title>
                    {{ $licenseInfo['uses'] ?? 0 }}
                </x-core::datagrid.item>

                <x-core::datagrid.item>
                    <x-slot:title>
                        {{ trans('plugins/license-manager::license-manager.product_license.form.parallel_uses') }}
                    </x-slot:title>
                    {{ $licenseInfo['parallel_uses'] ?? '—' }}
                </x-core::datagrid.item>

                <x-core::datagrid.item>
                    <x-slot:title>
                        {{ trans('plugins/license-manager::license-manager.product_license.form.expiry') }}
                    </x-slot:title>
                    {{ $licenseInfo['expires_at'] ?? '—' }}
                </x-core::datagrid.item>

                <x-core::datagrid.item>
                    <x-slot:title>
                        {{ trans('plugins/license-manager::license-manager.product_license.form.updates_till') }}
                    </x-slot:title>
                    {{ $licenseInfo['updates_until'] ?? '—' }}
                </x-core::datagrid.item>

                <x-core::datagrid.item>
                    <x-slot:title>
                        {{ trans('plugins/license-manager::license-manager.product_license.form.supported_till') }}
                    </x-slot:title>
                    {{ $licenseInfo['support_until'] ?? '—' }}
                </x-core::datagrid.item>
            </x-core::datagrid>

            @if (!empty($licenseInfo['customer_id']) || !empty($licenseInfo['email']) || !empty($licenseInfo['invoice']))
                <x-core::datagrid class="mt-3">
                    @if (!empty($licenseInfo['customer_id']))
                        <x-core::datagrid.item>
                            <x-slot:title>
                                {{ trans('plugins/license-manager::license-manager.product_license.form.client') }}
                            </x-slot:title>
                            {{ $licenseInfo['customer_id'] }}
                        </x-core::datagrid.item>
                    @endif

                    @if (!empty($licenseInfo['email']))
                        <x-core::datagrid.item>
                            <x-slot:title>
                                {{ trans('plugins/license-manager::license-manager.product_license.form.client_email') }}
                            </x-slot:title>
                            {{ $licenseInfo['email'] }}
                        </x-core::datagrid.item>
                    @endif

                    @if (!empty($licenseInfo['invoice']))
                        <x-core::datagrid.item>
                            <x-slot:title>
                                {{ trans('plugins/license-manager::license-manager.product_license.form.invoice_number') }}
                            </x-slot:title>
                            {{ $licenseInfo['invoice'] }}
                        </x-core::datagrid.item>
                    @endif
                </x-core::datagrid>
            @endif
        </x-core::card.body>
    </x-core::card>

    <x-core::card>
        <x-core::card.header>
            <x-core::card.title>
                {{ trans('plugins/license-manager::license-manager.bulk_generate.generated_licenses') }}
            </x-core::card.title>

            <div class="card-actions">
                <button type="button" class="btn btn-outline-primary" id="copy-all-licenses">
                    <x-core::icon name="ti ti-copy" />
                    {{ trans('plugins/license-manager::license-manager.bulk_generate.copy_all') }}
                </button>
            </div>
        </x-core::card.header>

        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th class="w-1">#</th>
                        <th>{{ trans('plugins/license-manager::license-manager.product_license.form.license_code') }}</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($licenseCodes as $index => $code)
                        <tr>
                            <td class="text-secondary">{{ $index + 1 }}</td>
                            <td><code class="font-monospace">{{ $code }}</code></td>
                            <td>
                                <x-core::copy :copyableState="$code" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-core::card>

    <div class="mt-3 d-flex gap-2">
        <a href="{{ route('lm.bulk-generate.index') }}" class="btn btn-primary">
            <x-core::icon name="ti ti-plus" />
            {{ trans('plugins/license-manager::license-manager.bulk_generate.generate_more') }}
        </a>
        <a href="{{ route('lm.licenses.index') }}" class="btn btn-outline-secondary">
            {{ trans('plugins/license-manager::license-manager.bulk_generate.back_to_licenses') }}
        </a>
    </div>

    <textarea id="all-license-codes" class="d-none">{{ implode("\n", $licenseCodes) }}</textarea>

    <script>
        document.getElementById('copy-all-licenses')?.addEventListener('click', function() {
            var textarea = document.getElementById('all-license-codes');
            navigator.clipboard.writeText(textarea.value).then(function() {
                Botble.showSuccess(@json(trans('plugins/license-manager::license-manager.bulk_generate.copied_to_clipboard')));
            });
        });
    </script>
@endsection
