@php
    use Botble\LicenseManager\Models\ApiKey;
    $apiKeys = ApiKey::query()->latest()->get();
@endphp

<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h3 class="card-title mb-0">{{ trans('plugins/license-manager::license-manager.api_key.title') }}</h3>
            <p class="text-muted mb-0 small">{{ trans('plugins/license-manager::license-manager.api_key.description') }}</p>
        </div>
        <a href="{{ route('lm.api-keys.create') }}" class="btn btn-primary">
            <x-core::icon name="ti ti-plus" />
            {{ trans('core/base::forms.create') }}
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>{{ trans('plugins/license-manager::license-manager.api_key.key') }}</th>
                    <th>{{ trans('plugins/license-manager::license-manager.api_key.type') }}</th>
                    <th>{{ trans('plugins/license-manager::license-manager.api_key.scopes') }}</th>
                    <th>{{ trans('plugins/license-manager::license-manager.api_key.expires_at') }}</th>
                    <th class="text-center">{{ trans('plugins/license-manager::license-manager.api_key.special') }}</th>
                    <th class="text-center">{{ trans('plugins/license-manager::license-manager.api_key.revoke') }}</th>
                    <th>{{ trans('core/base::tables.created_at') }}</th>
                    <th class="w-1"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($apiKeys as $apiKey)
                    <tr>
                        <td>
                            <code class="user-select-all">{{ substr($apiKey->key, 0, -8) }}********</code>
                        </td>
                        <td>
                            <span class="badge {{ $apiKey->type->value === 'internal' ? 'bg-blue text-blue-fg' : 'bg-green text-green-fg' }}">
                                {{ $apiKey->type->label() }}
                            </span>
                        </td>
                        <td>
                            @if(empty($apiKey->scopes))
                                <span class="text-muted">-</span>
                            @else
                                <span class="cursor-help" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ implode(', ', $apiKey->scopes) }}">
                                    <code>{{ count($apiKey->scopes) }} {{ trans('plugins/license-manager::license-manager.api_key.scopes') }}</code>
                                    <x-core::icon name="ti ti-info-circle" class="text-muted" />
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($apiKey->expires_at)
                                {{ $apiKey->expires_at->format('Y-m-d H:i') }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($apiKey->special)
                                <span class="badge bg-green text-green-fg">{{ trans('core/base::base.yes') }}</span>
                            @else
                                <span class="badge bg-secondary text-secondary-fg">{{ trans('core/base::base.no') }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($apiKey->revoked)
                                <span class="badge bg-red text-red-fg">{{ trans('core/base::base.yes') }}</span>
                            @else
                                <span class="badge bg-secondary text-secondary-fg">{{ trans('core/base::base.no') }}</span>
                            @endif
                        </td>
                        <td>{{ $apiKey->created_at->format('Y-m-d H:i') }}</td>
                        <td>
                            <div class="btn-list flex-nowrap">
                                <a href="{{ route('lm.api-keys.edit', $apiKey->id) }}" class="btn btn-sm btn-icon btn-ghost-primary" title="{{ trans('core/base::forms.edit') }}">
                                    <x-core::icon name="ti ti-edit" />
                                </a>
                                <button type="button" class="btn btn-sm btn-icon btn-ghost-danger btn-delete-api-key" data-url="{{ route('lm.api-keys.destroy', $apiKey->id) }}" title="{{ trans('core/base::forms.delete') }}">
                                    <x-core::icon name="ti ti-trash" />
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            {{ trans('core/base::tables.no_data') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.btn-delete-api-key').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const url = this.dataset.url;
                if (confirm('{{ trans('core/base::tables.confirm_delete') }}')) {
                    fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.error === false) {
                            window.location.reload();
                        } else {
                            alert(data.message || 'Error deleting API key');
                        }
                    })
                    .catch(() => {
                        alert('Error deleting API key');
                    });
                }
            });
        });
    });
</script>
