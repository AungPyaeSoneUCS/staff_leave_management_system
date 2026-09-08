@extends('layouts.app')

@section('title', __('staff.edit_leave_request'))

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="cu-card cu-card-body">
        <h2 class="cu-page-title mb-1">{{ __('staff.edit_leave_request') }}</h2>
        <p class="cu-muted mb-6">{{ __('staff.edit_leave_subtitle') }}</p>

        <form action="{{ route('staff.leave-requests.update', $leaveRequest) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="leave_type_id" class="cu-label">{{ __('common.leave_type') }}</label>
                <select name="leave_type_id" id="leave_type_id" class="cu-select">
                    @foreach($leaveTypes as $type)
                        <option value="{{ $type->id }}" {{ $leaveRequest->leave_type_id == $type->id ? 'selected' : '' }} data-requires-attachment="{{ $type->requires_attachment ? 1 : 0 }}" data-is-not-limited="{{ $type->is_not_limited ? 1 : 0 }}">
                            {{ $type->name }} ({{ $type->track_on_use ? __('common.count_on_use') : $type->annual_allocation.' '.__('common.days').'/'.__('common.year') }})
                        </option>
                    @endforeach
                </select>
                @error('leave_type_id')
                    <p class="cu-form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div id="start-date-group">
                    <label for="start_date" class="cu-label">{{ __('common.start_date') }}</label>
                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $leaveRequest->start_date->format('Y-m-d')) }}" class="cu-input" min="{{ now()->format('Y-m-d') }}">
                    @error('start_date')
                        <p class="cu-form-error">{{ $message }}</p>
                    @enderror
                </div>
                <div id="end-date-group">
                    <label for="end_date" class="cu-label">{{ __('common.end_date') }}</label>
                    <input type="date" name="end_date" id="end_date" value="{{ old('end_date', $leaveRequest->end_date ? $leaveRequest->end_date->format('Y-m-d') : '') }}" class="cu-input">
                    @error('end_date')
                        <p class="cu-form-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mb-4">
                <label for="reason" class="cu-label">{{ __('common.reason') }}</label>
                <textarea name="reason" id="reason" rows="4" class="cu-textarea">{{ old('reason', $leaveRequest->reason) }}</textarea>
                @error('reason')
                    <p class="cu-form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="attachment" class="cu-label">
                    {{ __('common.supporting_document') }}
                    <span id="attachment-required" class="text-red-500 {{ $leaveRequest->leaveType->requires_attachment ? '' : 'hidden' }}">*</span>
                </label>
                @if($leaveRequest->attachment_path)
                    <div class="mb-2">
                        <a href="{{ Storage::url($leaveRequest->attachment_path) }}" target="_blank" class="cu-link">
                            {{ __('common.view_document') }}
                        </a>
                        <div class="mt-2 flex items-center gap-2">
                            <input type="checkbox" name="remove_attachment" id="remove_attachment" value="1" class="rounded border-slate-300 text-cu-600 focus:ring-cu-500">
                            <label for="remove_attachment" class="text-sm text-slate-700">{{ __('staff.remove_attachment') }}</label>
                        </div>
                    </div>
                @endif
                <input type="file" name="attachment" id="attachment" class="cu-file" {{ $leaveRequest->leaveType->requires_attachment ? 'required' : '' }}>
                <p class="mt-1 text-sm text-slate-500">{{ __('common.file_hint') }}</p>
                @error('attachment')
                    <p class="cu-form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('staff.leave-requests.show', $leaveRequest) }}" class="cu-btn-secondary">{{ __('common.cancel') }}</a>
                <button type="submit" class="cu-btn-primary">{{ __('common.save') }}</button>
            </div>
        </form>
</div>
    </div>
</div>

@push('scripts')
<script>
const existingLeaves = @json($leaveRequests->where('id', '!=', $leaveRequest->id)->map(fn($r) => [
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
    const attachmentInput = document.getElementById('attachment');
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

document.getElementById('leave_type_id').addEventListener('change', function() {
    updateAttachmentRequirement();
    updateDateFields();
});

updateAttachmentRequirement();
updateDateFields();
updateEndDateMin();
</script>
@endpush
@endsection