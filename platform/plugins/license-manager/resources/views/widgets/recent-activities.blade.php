<div class="activities-list" style="max-height: 350px; overflow-y: auto;">
    @forelse($activities as $activity)
        <div class="activity-item border-bottom px-3 py-2">
            <small class="text-muted d-block">
                {{ \Carbon\Carbon::parse($activity->created_at)->format('d F, Y, g:i a') }}
            </small>
            <div class="activity-content">
                {!! \Botble\Base\Facades\BaseHelper::clean($activity->message) !!}
            </div>
        </div>
    @empty
        <div class="text-center text-muted py-4">
            {{ trans('plugins/license-manager::license-manager.dashboard_widgets.no_activities') }}
        </div>
    @endforelse
</div>
@if($activities->isNotEmpty())
    <div class="card-footer text-end">
        <a href="{{ route('lm.activity-logs.index') }}" class="btn btn-primary btn-sm">
            {{ trans('plugins/license-manager::license-manager.dashboard_widgets.view_all') }}
        </a>
    </div>
@endif
