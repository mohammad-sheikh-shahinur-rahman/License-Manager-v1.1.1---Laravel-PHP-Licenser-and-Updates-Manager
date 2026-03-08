<x-core::card class="mb-3">
    <x-core::card.header>
        <x-core::card.title>
            {{ trans('plugins/license-manager::license-manager.legacy_migration.detection_title') }}
        </x-core::card.title>
    </x-core::card.header>
    <x-core::card.body>
        <x-core::alert type="warning" class="mb-3">
            <x-core::icon name="ti ti-folder" class="me-1" />
            {!! trans('plugins/license-manager::license-manager.legacy_migration.version_files_note', ['path' => '<code>storage/app/version-files/</code>']) !!}
        </x-core::alert>
        @if ($hasTables)
            <div class="alert alert-info mb-3">
                <x-core::icon name="ti ti-info-circle" class="me-1" />
                {{ trans('plugins/license-manager::license-manager.legacy_migration.tables_found') }}
            </div>

            <div class="table-responsive mb-3">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>{{ trans('plugins/license-manager::license-manager.legacy_migration.table_name') }}</th>
                            <th>{{ trans('plugins/license-manager::license-manager.legacy_migration.status') }}</th>
                            <th>{{ trans('plugins/license-manager::license-manager.legacy_migration.record_count') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tables as $table => $exists)
                            <tr>
                                <td><code>{{ $table }}</code></td>
                                <td>
                                    @if ($exists)
                                        <span class="badge bg-green text-green-fg">
                                            <x-core::icon name="ti ti-check" class="me-1" />
                                            {{ trans('plugins/license-manager::license-manager.legacy_migration.found') }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary text-secondary-fg">
                                            {{ trans('plugins/license-manager::license-manager.legacy_migration.not_found') }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if ($exists)
                                        <strong>{{ number_format($counts[$table] ?? 0) }}</strong>
                                        {{ trans('plugins/license-manager::license-manager.legacy_migration.records') }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div id="migration-actions">
                <button
                    type="button"
                    class="btn btn-primary me-2"
                    id="start-migration-btn"
                    data-url="{{ route('lm.legacy-migration.migrate') }}"
                >
                    <x-core::icon name="ti ti-database-import" class="me-1" />
                    {{ trans('plugins/license-manager::license-manager.legacy_migration.start_migration') }}
                </button>

                <button
                    type="button"
                    class="btn btn-danger"
                    id="delete-tables-btn"
                    data-url="{{ route('lm.legacy-migration.delete') }}"
                    style="display: none;"
                >
                    <x-core::icon name="ti ti-trash" class="me-1" />
                    {{ trans('plugins/license-manager::license-manager.legacy_migration.delete_tables') }}
                </button>
            </div>

            <div id="migration-progress" style="display: none;" class="mt-3">
                <div class="alert alert-warning">
                    <x-core::icon name="ti ti-loader" class="ti-spin me-1" />
                    {{ trans('plugins/license-manager::license-manager.legacy_migration.migration_in_progress') }}
                </div>
            </div>

            <div id="migration-result" style="display: none;" class="mt-3"></div>
        @else
            <div class="alert alert-secondary">
                <x-core::icon name="ti ti-info-circle" class="me-1" />
                {{ trans('plugins/license-manager::license-manager.legacy_migration.no_tables_found') }}
            </div>
        @endif
    </x-core::card.body>
</x-core::card>

@if ($hasTables)
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const startBtn = document.getElementById('start-migration-btn');
        const deleteBtn = document.getElementById('delete-tables-btn');
        const progressDiv = document.getElementById('migration-progress');
        const resultDiv = document.getElementById('migration-result');

        const icons = {
            loader: @json(view('core/base::components.icon', ['name' => 'ti ti-loader', 'class' => 'ti-spin me-1'])->render()),
            check: @json(view('core/base::components.icon', ['name' => 'ti ti-check', 'class' => 'me-1'])->render()),
            x: @json(view('core/base::components.icon', ['name' => 'ti ti-x', 'class' => 'me-1'])->render())
        };

        const steps = ['products', 'customers', 'licenses', 'activations', 'versions', 'activity_logs', 'update_downloads', 'api_keys', 'settings'];
        const stepLabels = {
            products: @json(trans('plugins/license-manager::license-manager.legacy_migration.migrating_products')),
            customers: @json(trans('plugins/license-manager::license-manager.legacy_migration.migrating_customers')),
            licenses: @json(trans('plugins/license-manager::license-manager.legacy_migration.migrating_licenses')),
            activations: @json(trans('plugins/license-manager::license-manager.legacy_migration.migrating_activations')),
            versions: @json(trans('plugins/license-manager::license-manager.legacy_migration.migrating_versions')),
            activity_logs: @json(trans('plugins/license-manager::license-manager.legacy_migration.migrating_activity_logs')),
            update_downloads: @json(trans('plugins/license-manager::license-manager.legacy_migration.migrating_update_downloads')),
            api_keys: @json(trans('plugins/license-manager::license-manager.legacy_migration.migrating_api_keys')),
            settings: @json(trans('plugins/license-manager::license-manager.legacy_migration.migrating_settings'))
        };

        async function runMigration() {
            const results = {
                products: 0,
                customers: 0,
                licenses: 0,
                activations: 0,
                versions: 0,
                activity_logs: 0,
                update_downloads: 0,
                api_keys: 0,
                settings: 0
            };

            for (const step of steps) {
                let offset = 0;
                let hasMore = true;
                let batchCount = 0;

                while (hasMore) {
                    batchCount++;
                    const processed = results[step];
                    progressDiv.innerHTML = `<div class="alert alert-warning d-block">
                        ${icons.loader}
                        ${stepLabels[step]}... ${processed > 0 ? `(${processed.toLocaleString()} ${@json(trans('plugins/license-manager::license-manager.legacy_migration.records_processed'))})` : ''}
                    </div>`;

                    try {
                        const response = await fetch(startBtn.dataset.url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ step: step, offset: offset })
                        });

                        if (!response.ok) {
                            throw new Error(`Server error: ${response.status} ${response.statusText}`);
                        }

                        const text = await response.text();
                        let data;
                        try {
                            data = JSON.parse(text);
                        } catch (e) {
                            throw new Error('Server returned invalid response. Try again or check server logs.');
                        }

                        if (!data.success) {
                            throw new Error(data.error || data.message);
                        }

                        results[step] += data.count;
                        hasMore = data.has_more || false;
                        offset = data.next_offset || 0;
                    } catch (error) {
                        progressDiv.style.display = 'none';
                        resultDiv.innerHTML = `<div class="alert alert-danger d-block">
                            <h4 class="alert-title">${icons.x} ${@json(trans('plugins/license-manager::license-manager.legacy_migration.step_failed_at'))} ${step}</h4>
                            <p class="mb-0">${error.message}</p>
                        </div>`;
                        resultDiv.style.display = 'block';
                        startBtn.disabled = false;
                        return;
                    }
                }
            }

            progressDiv.style.display = 'none';
            resultDiv.innerHTML = `<div class="alert alert-success d-block">
                <h4 class="alert-title">${icons.check} ${@json(trans('plugins/license-manager::license-manager.legacy_migration.migration_completed'))}</h4>
                <ul class="mb-0 mt-2">
                    <li>${@json(trans('plugins/license-manager::license-manager.legacy_migration.products_created'))}: <strong>${results.products.toLocaleString()}</strong></li>
                    <li>${@json(trans('plugins/license-manager::license-manager.legacy_migration.customers_created'))}: <strong>${results.customers.toLocaleString()}</strong></li>
                    <li>${@json(trans('plugins/license-manager::license-manager.legacy_migration.licenses_created'))}: <strong>${results.licenses.toLocaleString()}</strong></li>
                    <li>${@json(trans('plugins/license-manager::license-manager.legacy_migration.activations_created'))}: <strong>${results.activations.toLocaleString()}</strong></li>
                    <li>${@json(trans('plugins/license-manager::license-manager.legacy_migration.versions_created'))}: <strong>${results.versions.toLocaleString()}</strong></li>
                    <li>${@json(trans('plugins/license-manager::license-manager.legacy_migration.activity_logs_created'))}: <strong>${results.activity_logs.toLocaleString()}</strong></li>
                    <li>${@json(trans('plugins/license-manager::license-manager.legacy_migration.update_downloads_created'))}: <strong>${results.update_downloads.toLocaleString()}</strong></li>
                    <li>${@json(trans('plugins/license-manager::license-manager.legacy_migration.api_keys_created'))}: <strong>${results.api_keys.toLocaleString()}</strong></li>
                    <li>${@json(trans('plugins/license-manager::license-manager.legacy_migration.settings_migrated'))}: <strong>${results.settings.toLocaleString()}</strong></li>
                </ul>
            </div>`;
            resultDiv.style.display = 'block';
            deleteBtn.style.display = 'inline-block';
            startBtn.disabled = false;
        }

        startBtn.addEventListener('click', function() {
            if (!confirm(@json(trans('plugins/license-manager::license-manager.legacy_migration.confirm_migration')))) {
                return;
            }

            startBtn.disabled = true;
            progressDiv.style.display = 'block';
            resultDiv.style.display = 'none';

            runMigration();
        });

        deleteBtn.addEventListener('click', function() {
            if (!confirm(@json(trans('plugins/license-manager::license-manager.legacy_migration.confirm_delete')))) {
                return;
            }

            deleteBtn.disabled = true;

            fetch(deleteBtn.dataset.url, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                    deleteBtn.disabled = false;
                }
            })
            .catch(error => {
                alert(error.message);
                deleteBtn.disabled = false;
            });
        });
    });
</script>
@endif
