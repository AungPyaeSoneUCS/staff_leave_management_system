@extends('layouts.app')

@section('title', __('staff.submit_leave_request'))

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="cu-card cu-card-body">
        <h2 class="cu-page-title mb-1">{{ __('staff.submit_leave_request') }}</h2>

        <form id="leave-request-form" action="{{ route('staff.leave-requests.store') }}" method="POST" enctype="multipart/form-data">
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
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_half_day" id="is_half_day" value="1" {{ old('is_half_day') ? 'checked' : '' }} class="rounded border-slate-300 text-cu-600 focus:ring-cu-500">
                    <span class="cu-label mb-0">{{ __('common.half_day') }}</span>
                </label>
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
                <label for="attachments" class="cu-btn-secondary inline-flex cursor-pointer">
                    Choose Files
                </label>
                <input type="file" name="attachments[]" id="attachments" class="hidden" multiple accept="image/*,.pdf">
                <div id="attachment-previews" class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4"></div>
                <p id="attachment-validation-error" class="cu-form-error hidden"></p>
                <p class="mt-1 text-sm text-slate-500">{{ __('common.file_hint') }}</p>
                @error('attachments')
                    <p class="cu-form-error">{{ $message }}</p>
                @enderror
            </div>

            <x-signature-pad targets="leave-request-form" :required="true" />

            <div class="flex justify-end gap-3">
                <a href="{{ route('staff.leave-requests.index') }}" class="cu-btn-secondary">{{ __('common.cancel') }}</a>
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

const attachmentInput = document.getElementById('attachments');
const previewContainer = document.getElementById('attachment-previews');
const attachmentValidationError = document.getElementById('attachment-validation-error');
const maxAttachments = 3;
const maxSingleAttachmentBytes = 2 * 1024 * 1024;
const maxAttachmentBytes = 6 * 1024 * 1024;
let selectedAttachments = [];

attachmentInput.addEventListener('change', function () {
    const newlySelectedAttachments = Array.from(this.files);
    const attachmentsAfterSelection = selectedAttachments.concat(newlySelectedAttachments);
    const totalAttachmentBytes = attachmentsAfterSelection.reduce(function (total, file) {
        return total + file.size;
    }, 0);
    const oversizedAttachment = newlySelectedAttachments.find(function (file) {
        return file.size > maxSingleAttachmentBytes;
    });

    if (oversizedAttachment) {
        attachmentValidationError.textContent = 'Each attachment must not exceed 2 MB.';
        attachmentValidationError.classList.remove('hidden');
        syncAttachmentInput();
        renderAttachmentPreviews();
        return;
    }

    if (attachmentsAfterSelection.length > maxAttachments) {
        attachmentValidationError.textContent = 'You can upload a maximum of 3 attachments.';
        attachmentValidationError.classList.remove('hidden');
        syncAttachmentInput();
        renderAttachmentPreviews();
        return;
    }

    if (totalAttachmentBytes > maxAttachmentBytes) {
        attachmentValidationError.textContent = 'The total size of all attachments must not exceed 6 MB.';
        attachmentValidationError.classList.remove('hidden');
        syncAttachmentInput();
        renderAttachmentPreviews();
        return;
    }

    attachmentValidationError.classList.add('hidden');
    selectedAttachments = selectedAttachments.concat(newlySelectedAttachments);
    syncAttachmentInput();
    renderAttachmentPreviews();
});

function syncAttachmentInput() {
    const dataTransfer = new DataTransfer();
    selectedAttachments.forEach(function (file) {
        dataTransfer.items.add(file);
    });
    attachmentInput.files = dataTransfer.files;
}

function renderAttachmentPreviews() {
    previewContainer.innerHTML = '';

    selectedAttachments.forEach(function (file, index) {
        const previewTile = document.createElement('div');
        previewTile.className = 'relative overflow-hidden rounded-lg border border-slate-200 bg-white';

        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'absolute right-1 top-1 z-10 flex h-7 w-7 items-center justify-center rounded-full border border-red-200 bg-white text-red-600 shadow-sm hover:bg-red-50';
        removeButton.innerHTML = '<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M18 6L6 18"></path></svg>';
        removeButton.setAttribute('aria-label', 'Remove selected file');
        removeButton.title = 'Remove selected file';
        removeButton.addEventListener('click', function () {
            selectedAttachments.splice(index, 1);
            syncAttachmentInput();
            attachmentValidationError.classList.add('hidden');
            renderAttachmentPreviews();
        });
        previewTile.appendChild(removeButton);

        if (file.type.startsWith('image/')) {
            const previewImage = document.createElement('img');
            previewImage.src = URL.createObjectURL(file);
            previewImage.alt = '';
            previewImage.className = 'h-32 w-full object-cover';
            previewImage.onload = function () {
                URL.revokeObjectURL(previewImage.src);
            };
            previewTile.appendChild(previewImage);
        } else {
            const fileType = document.createElement('div');
            fileType.className = 'flex h-32 items-center justify-center bg-slate-50 text-sm font-medium uppercase text-slate-500';
            fileType.textContent = file.type.split('/').pop() || 'file';
            previewTile.appendChild(fileType);
        }

        previewContainer.appendChild(previewTile);
    });
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
    updateHalfDayVisibility();
});

function updateHalfDayVisibility() {
    const isHalfDay = document.getElementById('is_half_day').checked;
    const endDateGroup = document.getElementById('end-date-group');
    const endInput = document.getElementById('end_date');
    const startInput = document.getElementById('start_date');

    if (isHalfDay) {
        endDateGroup.classList.add('hidden');
        endInput.value = startInput.value || '';
        endInput.removeAttribute('required');
    } else {
        endDateGroup.classList.remove('hidden');
    }
}

document.getElementById('is_half_day').addEventListener('change', updateHalfDayVisibility);
document.getElementById('start_date').addEventListener('change', function () {
    if (document.getElementById('is_half_day').checked) {
        document.getElementById('end_date').value = this.value;
    }
});

updateHalfDayVisibility();

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
