<div id="alert-modal" class="fixed inset-0 z-[999] hidden" aria-labelledby="alert-modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="closeAlertModal()"></div>

    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-6">
            <div class="relative transform overflow-hidden rounded-2xl bg-white/95 backdrop-blur-xl border border-slate-200/80 shadow-2xl shadow-slate-950/30 text-left transition-all sm:my-8 sm:w-full sm:max-w-sm">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="flex items-start gap-3">
                        <div class="mx-auto flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-50 sm:mx-0">
                            <svg class="h-5 w-5 text-amber-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.88c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.516-2.625l6.28-10.88zM10 6a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 6zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-base font-semibold text-slate-900" id="alert-modal-title">{{ __('common.alert') }}</h3>
                            <div class="mt-2">
                                <p class="text-sm text-slate-500" id="alert-modal-message"></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-2 bg-slate-50/80 px-4 py-3 sm:px-6 border-t border-slate-100">
                    <button type="button" class="cu-btn-primary" onclick="closeAlertModal()">{{ __('common.ok') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>
