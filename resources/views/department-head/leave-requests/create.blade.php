@extends('layouts.app')

@section('title', __('staff.submit_leave_request'))

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="cu-card cu-card-body">
        <h2 class="cu-page-title mb-1">{{ __('staff.submit_leave_request') }}</h2>

        <form id="leave-request-form" action="{{ route('department-head.leave-requests.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-4">
                <label for="leave_type_id" class="cu-label">{{ __('common.leave_type') }}</label>
                <select name="leave_type_id" id="leave_type_id" class="cu-select">
                    <option value="">{{ __('common.select_leave_type') }}</option>
                    @foreach($leaveTypes as $type)
                        <option value="{{ $type->id }}" data-requires-attachment="{{ $type->requires_attachment ? 1 : 0 }}" data-is-not-limited="{{ $type->is_not_limited ? 1 : 0 }}" data-description="{{ $type->description }}" data-description-mm="{{ $type->description_mm ?? $type->description }}">
                            {{ app()->getLocale() == 'my' ? ($type->name_mm ?? $type->name) : $type->name }} ({{ $type->track_on_use ? __('common.count_on_use') : $type->annual_allocation.' '.__('common.days').'/'.__('common.year') }})
                        </option>
                    @endforeach
                </select>
                @error('leave_type_id')
                    <p class="cu-form-error">{{ $message }}</p>
                @enderror
            </div>

            <div id="leave-type-description" class="mb-4 hidden rounded-xl bg-slate-50 border border-slate-100 p-4">
                <p id="leave-type-description-text" class="text-sm leading-6 text-slate-700"></p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div id="start-date-group">
                    <label for="start_date" class="cu-label">{{ __('common.start_date') }}</label>
                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}" class="cu-input" min="{{ now()->format('Y-m-d') }}">
                    @error('start_date')
                        <p class="cu-form-error">{{ $message }}</p>
                    @enderror
                </div>
                <div id="end-date-group">
                    <label for="end_date" class="cu-label">{{ __('common.end_date') }}</label>
                    <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}" class="cu-input">
                    @error('end_date')
                        <p class="cu-form-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mb-4">
                <label for="reason" class="cu-label">{{ __('common.reason') }}</label>
                <textarea name="reason" id="reason" rows="4" class="cu-textarea"
                          placeholder="{{ __('common.please_provide_reason') ?? 'Please provide reason for leave...' }}"></textarea>
                @error('reason')
                    <p class="cu-form-error">{{ $message }}</p>
                @enderror
            </div>

            @if(auth()->user()->isStaff() || auth()->user()->isDepartmentHead())
            <div class="mb-4">
                <label for="duty_exchange_user_id" class="cu-label">{{ __('common.duty_exchange_staff') }}</label>
                <select name="duty_exchange_user_id" id="duty_exchange_user_id" class="cu-select">
                    <option value="">{{ __('common.none') }}</option>
                    @foreach(get_duty_exchange_candidates(auth()->user(), auth()->id()) as $candidate)
                        <option value="{{ $candidate->id }}">
                            {{ app()->getLocale() == 'my' ? ($candidate->name_mm ?? $candidate->name) : $candidate->name }}
                            @if($candidate->position)({{ $candidate->position }})@endif
                        </option>
                    @endforeach
                </select>
                @error('duty_exchange_user_id')
                    <p class="cu-form-error">{{ $message }}</p>
                @enderror
            </div>
            @endif

            <div class="mb-6">
                <label for="attachments" class="cu-label">
                    {{ __('common.supporting_document') }}
                    <span id="attachment-required" class="text-red-500 hidden">*</span>
                </label>
                <input type="file" name="attachments[]" id="attachments" class="cu-file" multiple>
                <p class="mt-1 text-sm text-slate-500">{{ __('common.file_hint') }}</p>
                @error('attachments')
                    <p class="cu-form-error">{{ $message }}</p>
                @enderror
            </div>

            <x-signature-pad targets="leave-request-form" :required="true" />

            <div class="flex justify-end gap-3">
                <a href="{{ route('department-head.leave-requests.index') }}" class="cu-btn-secondary">{{ __('common.cancel') }}</a>
                <button type="submit" class="cu-btn-primary">{{ __('common.submit') }}</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const existingLeaves = @json($existingLeaves->map(fn($r) => [
    'start' => $r->start_date->format('Y-m-d'),
    'end' => $r->end_date ? $r->end_date->format('Y-m-d') : $r->start_date->format('Y-m-d'),
]));
const startInput = document.getElementById('start_date');
const endInput = document.getElementById('end_date');
const today = '{{ now()->format('Y-m-d') }}';

function isDateBlocked(dateStr) {
    return existingLeaves.some(leave => {
        const s = new Date(leave.start);
        const e = new Date(leave.end);
        const d = new Date(dateStr);
        return d >= s && d <= e;
    });
}

startInput.addEventListener('change', () => {
    if (startInput.value < today) {
        startInput.value = today;
    }
    if (isDateBlocked(startInput.value)) {
        showAlertModal('{{ __('staff.date_overlaps_existing') }}');
        startInput.value = '';
    }
    updateEndDateMin();
});

endInput.addEventListener('change', () => {
    if (startInput.value && endInput.value < startInput.value) {
        showAlertModal('{{ __('staff.end_date_after_start') }}');
        endInput.value = '';
    }
    if (isDateBlocked(endInput.value)) {
        showAlertModal('{{ __('staff.date_overlaps_existing') }}');
        endInput.value = '';
    }
});

function updateEndDateMin() {
    if (startInput.value) {
        endInput.min = startInput.value;
    }
}

function updateAttachmentRequirement() {
    const select = document.getElementById('leave_type_id');
    const attachmentInput = document.getElementById('attachments');
    const requiredIndicator = document.getElementById('attachment-required');
    const selectedOption = select.options[select.selectedIndex];

    if (selectedOption && selectedOption.dataset.requiresAttachment === '1') {
        attachmentInput.setAttribute('required', 'required');
        requiredIndicator.classList.remove('hidden');
    } else {
        attachmentInput.removeAttribute('required');
        requiredIndicator.classList.add('hidden');
    }
}

function updateLeaveTypeDescription() {
    const select = document.getElementById('leave_type_id');
    const container = document.getElementById('leave-type-description');
    const text = document.getElementById('leave-type-description-text');
    const selectedOption = select.options[select.selectedIndex];

    if (selectedOption && selectedOption.value) {
        const isMy = '{{ app()->getLocale() }}' === 'my';
        const desc = isMy ? (selectedOption.dataset.descriptionMm || selectedOption.dataset.description) : selectedOption.dataset.description;
        if (desc) {
            text.textContent = desc;
            // container.classList.remove('hidden');
            return;
        }
    }
    // container.classList.add('hidden');
}

document.getElementById('leave_type_id').addEventListener('change', function() {
    updateAttachmentRequirement();
    updateDateFields();
    updateLeaveTypeDescription();
});

function updateDateFields() {
    const select = document.getElementById('leave_type_id');
    const startDateGroup = document.getElementById('start-date-group');
    const endDateGroup = document.getElementById('end-date-group');
    const selectedOption = select.options[select.selectedIndex];

    if (selectedOption && selectedOption.dataset.isNotLimited === '1') {
        startDateGroup.classList.remove('hidden');
        endDateGroup.classList.add('hidden');
        const startInput = document.getElementById('start_date');
        const endInput = document.getElementById('end_date');
        if (!startInput.value) {
            startInput.value = '{{ now()->format('Y-m-d') }}';
        }
        endInput.value = '';
    } else {
        startDateGroup.classList.remove('hidden');
        endDateGroup.classList.remove('hidden');
    }
}

updateAttachmentRequirement();
updateDateFields();
updateLeaveTypeDescription();
</script>
@endpush
@endsection
