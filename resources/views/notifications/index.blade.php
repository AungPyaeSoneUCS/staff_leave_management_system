@extends('layouts.app')

@section('title', __('notifications.all_title'))

@section('content')
<div class="space-y-6">
    <div class="cu-card-header">
        <div>
            <h2 class="cu-page-title">{{ __('notifications.all_title') }}</h2>
            <p class="cu-muted mt-1">{{ __('notifications.all_subtitle') }}</p>
        </div>
        <button onclick="markAllNotificationsRead()" class="cu-btn-primary" id="page-mark-all-read-btn">
            {{ __('notifications.mark_all_read') }}
        </button>
    </div>

    <div class="cu-card cu-card-body">
        @if($notifications->isEmpty())
            <div class="text-center py-12">
                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                <p class="text-sm text-slate-500">{{ __('notifications.no_notifications') }}</p>
            </div>
        @else
            <div class="divide-y divide-slate-100">
                @foreach($notifications as $notification)
                    @php
                        $data = $notification->data;
                        $title = $data['title'] ?? 'Notification';
                        $message = $data['message'] ?? '';
                        $leaveRequestId = $data['leave_request_id'] ?? null;

                        $url = '#';
                        if ($leaveRequestId) {
                            $url = match (true) {
                                auth()->user()->isStaff() => route('staff.leave-requests.show', $leaveRequestId),
                                auth()->user()->isDepartmentHead() => route('department-head.approvals.show', $leaveRequestId),
                                auth()->user()->isAdmin() => route('central-admin.approvals.show', $leaveRequestId),
                                default => '#',
                            };
                        }
                        $isUnread = is_null($notification->read_at);
                    @endphp
                    <div class="px-4 py-4 hover:bg-slate-50/50 transition-colors {{ $isUnread ? 'bg-primary-50/30' : '' }}">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 mt-1">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full {{ $isUnread ? 'bg-primary-100 text-primary-600' : 'bg-slate-100 text-slate-500' }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <p class="text-sm font-semibold text-slate-900 truncate">{{ $title }}</p>
                                    @if($isUnread)
                                        <span class="flex-shrink-0 h-2 w-2 rounded-full bg-primary-500"></span>
                                    @endif
                                </div>
                                <p class="text-sm text-slate-600 line-clamp-2">{{ $message }}</p>
                                <div class="flex items-center gap-3 mt-2">
                                    <span class="text-xs text-slate-400">{{ $notification->created_at->diffForHumans() }}</span>
                                    @if($isUnread)
                                        <form method="POST" action="{{ route('notifications.read', $notification) }}" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="text-xs font-medium text-primary-600 hover:text-primary-700 transition-colors">
                                                {{ __('notifications.mark_read') }}
                                            </button>
                                        </form>
                                    @endif
                                    @if($url && $url !== '#')
                                        <a href="{{ $url }}" class="text-xs font-medium text-primary-600 hover:text-primary-700 transition-colors">
                                            {{ __('notifications.view_request') }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function markAllNotificationsRead() {
    fetch('/notifications/read-all', {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json',
        }
    }).then(function() {
        window.location.reload();
    });
}
</script>
@endpush
