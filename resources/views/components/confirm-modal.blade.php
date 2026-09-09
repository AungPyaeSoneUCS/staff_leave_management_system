<div id="confirm-modal" class="fixed inset-0 z-[999] hidden" aria-labelledby="confirm-modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="closeConfirmModal()"></div>

    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-6">
            <div class="relative transform overflow-hidden rounded-2xl bg-white/95 backdrop-blur-xl border border-slate-200/80 shadow-2xl shadow-slate-950/30 text-left transition-all sm:my-8 sm:w-full sm:max-w-sm">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="flex items-start gap-3">
                        <div class="mx-auto flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-rose-50 sm:mx-0">
                            <svg class="h-5 w-5 text-rose-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-base font-semibold text-slate-900" id="confirm-modal-title">{{ __('common.confirm') }}</h3>
                            <div class="mt-2">
                                <p class="text-sm text-slate-500" id="confirm-modal-message"></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-2 bg-slate-50/80 px-4 py-3 sm:px-6 border-t border-slate-100">
                    <button type="button" class="cu-btn-amber-nude" onclick="closeConfirmModal()">{{ __('common.cancel') }}</button>
                    <button type="button" id="confirm-modal-confirm-btn" class="cu-btn-danger" onclick="confirmConfirmModal()">{{ __('common.yes') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>
