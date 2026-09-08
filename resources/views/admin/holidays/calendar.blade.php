@extends('layouts.app')

@section('title', __('admin.holiday_calendar'))

@section('content')
<div class="space-y-6">
    <div class="cu-card-header">
        <div>
            <h2 class="cu-page-title">{{ __('admin.holiday_calendar') }}</h2>
            <p class="cu-muted mt-1">{{ __('admin.holiday_calendar_subtitle') }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.holidays.index') }}" class="cu-btn-secondary">
                {{ __('admin.list_view') }}
            </a>
        </div>
    </div>

    <div class="cu-card cu-card-body">
        <div class="flex items-center justify-between mb-6">
            <button onclick="changeMonth(-1)" class="cu-btn-secondary">
                &larr; {{ __('common.previous') }}
            </button>
            <h3 class="text-lg font-semibold text-slate-900">
                {{\App\Support\MyanmarDateFormatter::format(\Carbon\Carbon::create($year, $month, 1), 'F Y')}}
            </h3>
            <button onclick="changeMonth(1)" class="cu-btn-secondary">
                {{ __('common.next') }} &rarr;
            </button>
        </div>

        <div class="grid grid-cols-7 gap-px bg-slate-200 rounded-xl overflow-hidden">
            @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                <div class="bg-slate-50 p-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    {{ $day }}
                </div>
            @endforeach

            @php
                $startOfMonth = \Carbon\Carbon::create($year, $month, 1);
                $endOfMonth = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();
                $startDayOfWeek = $startOfMonth->dayOfWeek;
                $daysInMonth = $endOfMonth->day;
            @endphp

            @for($i = 0; $i < $startDayOfWeek; $i++)
                <div class="bg-slate-50/50 p-3 min-h-[100px]"></div>
            @endfor

            @for($day = 1; $day <= $daysInMonth; $day++)
                @php
                    $currentDate = \Carbon\Carbon::create($year, $month, $day);
                    $dateKey = $currentDate->format('Y-m-d');
                    $holiday = $holidays->get($dateKey);
                    $isWeekend = $currentDate->isWeekend();
                    $isToday = $dateKey === $today;
                    $isReplacement = $holiday && $holiday->replaced_holiday_id !== null;
                @endphp
                <div class="bg-white p-2 min-h-[100px] {{ $isWeekend ? 'bg-slate-50' : '' }} {{ $isToday ? 'ring-2 ring-primary-500 ring-inset' : '' }} hover:bg-slate-50 transition-colors cursor-pointer"
                     onclick="openModal('{{ $dateKey }}')">
                    <div class="flex items-center justify-between">
                        <div class="text-sm font-medium {{ $isToday ? 'text-primary-600' : ($isWeekend ? 'text-slate-500' : 'text-slate-700') }}">
                            {{ $day }}
                        </div>
                        @if($holiday)
                            <input type="checkbox" class="holiday-checkbox rounded border-slate-300 text-cu-600 focus:ring-cu-500" value="{{ $dateKey }}" data-id="{{ $holiday->id }}" onclick="event.stopPropagation()">
                        @endif
                    </div>
                    @if($holiday)
                        <div class="mt-1 p-1.5 rounded bg-red-50 border border-red-200 cursor-pointer" onclick="openModal('{{ $dateKey }}')">
                            <p class="text-xs font-medium text-red-800 truncate">{{ app()->getLocale() == 'my' ? ($holiday->name_mm ?? $holiday->name) : $holiday->name }}</p>
                            <div class="flex items-center gap-1 mt-0.5">
                                @if($holiday->is_recurring)
                                    <span class="text-[10px] text-red-600">↻ {{ __('admin.recurring') }}</span>
                                @endif
                                @if($isReplacement)
                                    <span class="text-[10px] text-amber-700">⇄ {{ __('admin.replacement') }}</span>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            @endfor
        </div>
    </div>
</div>

<div id="holiday-modal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-slate-900" id="modal-title">{{ __('admin.add_holiday') }}</h3>
            <button onclick="closeModal()" class="text-slate-500 hover:text-slate-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <form id="holiday-form" onsubmit="saveHoliday(event)">
            @csrf
            <input type="hidden" name="date" id="holiday-date">
            <input type="hidden" name="id" id="holiday-id">

            <div class="mb-4">
                <label for="holiday-name" class="cu-label">{{ __('common.name') }}</label>
                <input type="text" name="name" id="holiday-name" class="cu-input" required>
            </div>

            <div class="mb-4">
                <label for="holiday-name-mm" class="cu-label">{{ __('common.name') }} (မြန်မာ)</label>
                <input type="text" name="name_mm" id="holiday-name-mm" class="cu-input">
            </div>

            <div class="mb-4">
                <label for="holiday-description" class="cu-label">{{ __('common.description') }}</label>
                <textarea name="description" id="holiday-description" rows="3" class="cu-textarea"></textarea>
            </div>

            <div class="mb-4">
                <label for="holiday-description-mm" class="cu-label">{{ __('common.description') }} (မြန်မာ)</label>
                <textarea name="description_mm" id="holiday-description-mm" rows="3" class="cu-textarea"></textarea>
            </div>

            <div class="mb-6 flex items-center gap-2">
                <input type="checkbox" name="is_recurring" id="holiday-recurring" value="1" class="rounded border-slate-300 text-cu-600 focus:ring-cu-500">
                <label for="holiday-recurring" class="text-sm text-slate-700">{{ __('admin.recurring_holiday') }}</label>
            </div>

            <div class="mb-6 flex items-center gap-2">
                <input type="checkbox" name="is_default" id="holiday-default" value="1" class="rounded border-slate-300 text-cu-600 focus:ring-cu-500">
                <label for="holiday-default" class="text-sm text-slate-700">{{ __('admin.default_holiday') }}</label>
            </div>

            <div class="mb-4">
                <label for="replaced_holiday_id" class="cu-label">{{ __('admin.replaces_holiday') }}</label>
                <select name="replaced_holiday_id" id="replaced_holiday_id" class="cu-select">
                    <option value="">{{ __('common.select_option') }}</option>
                    @foreach(\App\Models\Holiday::where('is_default', true)->get() as $default)
                        <option value="{{ $default->id }}">{{ app()->getLocale() == 'my' ? ($default->name_mm ?? $default->name) : $default->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label for="replacement_note" class="cu-label">{{ __('admin.replacement_note') }}</label>
                <textarea name="replacement_note" id="replacement_note" rows="2" class="cu-textarea" placeholder="{{ __('admin.replacement_note_placeholder') }}"></textarea>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" onclick="deleteHoliday()" id="delete-btn" class="cu-btn-danger hidden">{{ __('common.delete') }}</button>
                <button type="button" onclick="closeModal()" class="cu-btn-secondary">{{ __('common.cancel') }}</button>
                <button type="submit" class="cu-btn-primary">{{ __('common.save') }}</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
let currentDate = '{{ $dateKey ?? \Carbon\Carbon::create($year, $month, 1)->format('Y-m-d') }}';
let isEditing = false;

function changeMonth(delta) {
    const url = new URL(window.location.href);
    const date = new Date(currentDate);
    date.setDate(1);
    date.setMonth(date.getMonth() + delta);
    url.searchParams.set('month', date.getMonth() + 1);
    url.searchParams.set('year', date.getFullYear());
    window.location.href = url.toString();
}

    function openModal(date) {
        currentDate = date;
        isEditing = false;
        document.getElementById('modal-title').textContent = '{{ __('admin.add_holiday') }}';
        document.getElementById('holiday-form').reset();
        document.getElementById('holiday-date').value = date;
        document.getElementById('holiday-id').value = '';
        document.getElementById('delete-btn').classList.add('hidden');

        fetch(`{{ route('admin.holidays.by-date') }}?date=${date}`)
            .then(res => res.json())
            .then(data => {
                if (data.holiday) {
                    isEditing = true;
                    document.getElementById('modal-title').textContent = '{{ __('admin.edit_holiday') }}';
                    document.getElementById('holiday-id').value = data.holiday.id;
                    document.getElementById('holiday-name').value = data.holiday.name;
                    document.getElementById('holiday-name-mm').value = data.holiday.name_mm || '';
                    document.getElementById('holiday-description').value = data.holiday.description || '';
                    document.getElementById('holiday-description-mm').value = data.holiday.description_mm || '';
                    document.getElementById('holiday-recurring').checked = data.holiday.is_recurring;
                    document.getElementById('holiday-default').checked = data.holiday.is_default;
                    document.getElementById('replaced_holiday_id').value = data.holiday.replaced_holiday_id || '';
                    document.getElementById('replacement_note').value = data.holiday.replacement_note || '';
                    document.getElementById('delete-btn').classList.remove('hidden');
                }
            });

    document.getElementById('holiday-modal').classList.remove('hidden');
    document.getElementById('holiday-modal').classList.add('flex');
}

function closeModal() {
    document.getElementById('holiday-modal').classList.add('hidden');
    document.getElementById('holiday-modal').classList.remove('flex');
}

function saveHoliday(event) {
    event.preventDefault();

    const form = event.target;
    const formData = new FormData(form);
    const id = formData.get('id');

    const url = id ? `{{ route('admin.holidays.update-ajax', ['holiday' => ':id']) }}`.replace(':id', id) : `{{ route('admin.holidays.store-ajax') }}`;
    const method = id ? 'PUT' : 'POST';

    const data = {
        name: formData.get('name'),
        name_mm: formData.get('name_mm'),
        date: formData.get('date'),
        description: formData.get('description'),
        description_mm: formData.get('description_mm'),
        is_recurring: formData.has('is_recurring'),
        is_default: formData.has('is_default'),
        replaced_holiday_id: formData.get('replaced_holiday_id') || null,
        replacement_note: formData.get('replacement_note') || null,
        _token: '{{ csrf_token() }}'
    };

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        }
    })
    .catch(err => {
        console.error(err);
        alert('{{ __('flash.error') }}');
    });
}

function deleteHoliday() {
    const id = document.getElementById('holiday-id').value;
    if (!id) return;

    showConfirmModal('{{ __('admin.delete_holiday_confirm') }}').then(function(confirmed) {
        if (!confirmed) return;

        fetch(`{{ route('admin.holidays.destroy-ajax', ['holiday' => ':id']) }}`.replace(':id', id), {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            }
        });
    });
}

document.getElementById('holiday-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>
@endpush
@endsection
