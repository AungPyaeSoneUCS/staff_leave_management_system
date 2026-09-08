<div id="notification-dropdown" class="absolute right-0 mt-2 w-80 bg-white rounded-2xl border border-slate-200/80 shadow-lg shadow-slate-200/50 z-50 hidden">
    <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
        <h3 class="text-sm font-semibold text-slate-900">{{ __('nav.notifications') }}</h3>
        <button onclick="markAllNotificationsRead()" class="text-xs font-medium text-primary-600 hover:text-primary-700 transition-colors" id="mark-all-read-btn" style="display:none">
            {{ __('nav.mark_all_read') }}
        </button>
    </div>
    <div id="notification-list" class="max-h-80 overflow-y-auto">
        <div class="px-4 py-8 text-center" id="notification-loading">
            <svg class="w-6 h-6 mx-auto text-slate-300 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="text-xs text-slate-400 mt-2">{{ __('nav.loading_notifications') }}</p>
        </div>
    </div>
    <div class="px-4 py-2 border-t border-slate-100" id="notification-view-all" style="display:none">
        <a href="{{ route('notifications.all') }}" class="block text-center text-xs font-medium text-primary-600 hover:text-primary-700 transition-colors">
            {{ __('nav.view_all_notifications') }}
        </a>
    </div>
</div>