@extends('layouts.app')

@section('title', __('admin.import_leave'))

@section('content')
<div class="space-y-6">
    <div class="cu-card-header">
        <div>
            <h2 class="cu-page-title">{{ __('admin.import_leave') }}</h2>
            <p class="cu-muted mt-1">{{ __('admin.import_leave_subtitle') }}</p>
        </div>
    </div>

    <div class="cu-card cu-card-body">
        <h3 class="text-base font-semibold text-slate-800">{{ __('admin.custom_import') }}</h3>
        <p class="cu-muted mt-1">{{ __('admin.custom_import_hint') }}</p>

        <form method="POST" action="{{ route('admin.leave-import.custom') }}" id="custom-import-form" class="mt-4">
            @csrf

            <div class="space-y-3" id="custom-import-rows"></div>

            <div class="mt-4 flex items-center gap-3">
                <button type="button" id="add-custom-row" class="cu-btn-secondary">{{ __('admin.add_staff') }}</button>
                <p id="custom-import-message" class="cu-form-error text-sm"></p>
            </div>

            <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-slate-100">
                <button type="submit" class="cu-btn-primary">{{ __('common.save') }}</button>
            </div>
        </form>
    </div>

    <div class="cu-card cu-card-body">
        <form action="{{ route('admin.leave-import.preview') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="import_file" class="cu-label">{{ __('common.file') }}</label>
                    <input type="file" name="import_file" id="import_file" accept=".xlsx,.csv" class="cu-input" required>
                    <p class="text-xs text-slate-500 mt-1">{{ __('common.xlsx_or_csv_format') }}</p>
                    @error('import_file')
                        <p class="cu-form-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-slate-100">
                <a href="{{ route('admin.leave-import.template') }}" class="cu-btn-secondary">{{ __('admin.download_template') }}</a>
                <button type="submit" class="cu-btn-primary">{{ __('common.next') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
    <datalist id="custom-staff-list">
        @foreach($staff as $user)
            <option value="{{ $user->name }} ({{ $user->staff_id ?: '-' }})"></option>
            @if($user->staff_id)
                <option value="{{ $user->staff_id }}"></option>
            @endif
        @endforeach
    </datalist>

    <script>
        (function () {
            var staffData = @json($staff);
            var today = '{{ $today }}';
            var calculateUrl = '{{ route('admin.leave-import.calculate-total-days') }}';
            var byId = {}, byStaffId = {}, byName = {}, byFull = {}, byEmail = {};

            staffData.forEach(function (s) {
                byId[s.id] = s;
                if (s.staff_id) byStaffId[s.staff_id] = s;
                if (s.email) byEmail[s.email] = s;
                if (s.name) {
                    byName[s.name] = s;
                    byFull[(s.name + ' (' + (s.staff_id || '-') + ')')] = s;
                }
            });

            var rowIndex = 0;
            var rowsContainer = document.getElementById('custom-import-rows');

            function resolveStaff(value) {
                value = String(value || '').trim();
                if (!value) return null;
                if (byId[value]) return byId[value];
                if (byStaffId[value]) return byStaffId[value];
                if (byEmail[value]) return byEmail[value];
                if (byFull[value]) return byFull[value];
                return byName[value] || null;
            }

            function fmtDays(v) {
                var n = Number(v);
                return Number.isInteger(n) ? String(n) : String(n);
            }

            function recalc(row) {
                var start = row.querySelector('.custom-start').value;
                var half = row.querySelector('.custom-half').checked;
                var total = row.querySelector('.custom-total');
                var message = document.getElementById('custom-import-message');
                var params = new URLSearchParams({ start_date: start, is_half_day: half ? '1' : '0' });
                var end = row.querySelector('.custom-end').value;

                if (!start) {
                    total.value = '';
                    return;
                }

                if (end) params.set('end_date', end);

                fetch(calculateUrl + '?' + params.toString(), {
                    headers: { 'Accept': 'application/json' }
                }).then(function (response) {
                    return response.json().then(function (data) {
                        if (!response.ok) {
                            message.textContent = data.message || '';
                            total.value = '';
                            return;
                        }
                        message.textContent = '';
                        total.value = fmtDays(data.total_days);
                    });
                }).catch(function () {
                    message.textContent = '';
                });
            }

            function buildRow() {
                var index = rowIndex++;
                var div = document.createElement('div');
                div.className = 'grid grid-cols-2 md:grid-cols-12 gap-3 items-end rounded-xl border border-slate-200 p-3 bg-white';
                div.innerHTML = '' +
                    '<div class="col-span-2 md:col-span-3">' +
                    '    <label class="cu-label mb-1">{{ __('common.staff') }}</label>' +
                    '    <input type="text" list="custom-staff-list" autocomplete="off" placeholder="{{ __('admin.staff_name_or_id') }}" class="custom-staff-input cu-input" />' +
                    '    <input type="hidden" class="custom-user-id" />' +
                    '</div>' +
                    '<div class="col-span-2 md:col-span-2">' +
                    '    <label class="cu-label mb-1">{{ __('common.leave_type') }}</label>' +
                    '    <select class="custom-leave-type cu-select w-full">' +
                    @foreach($leaveTypes as $leaveType)
                    '        <option value="{{ $leaveType->id }}">{{ $leaveType->name }} ({{ $leaveType->code }})</option>' +
                    @endforeach
                    '    </select>' +
                    '</div>' +
                    '<div class="col-span-1 md:col-span-2">' +
                    '    <label class="cu-label mb-1">{{ __('common.start_date') }}</label>' +
                    '    <input type="date" value="' + today + '" class="custom-start cu-input" />' +
                    '</div>' +
                    '<div class="col-span-1 md:col-span-2">' +
                    '    <label class="cu-label mb-1">{{ __('common.end_date') }}</label>' +
                    '    <input type="date" class="custom-end cu-input" />' +
                    '</div>' +
                    '<div class="col-span-1 md:col-span-1 flex items-end pb-2">' +
                    '    <label class="gap-1 inline-flex items-center text-sm text-slate-700">' +
                    '        <input type="checkbox" class="custom-half h-4 w-4 rounded border-slate-300" /> {{ __('common.half_day') }}' +
                    '    </label>' +
                    '</div>' +
                    '<div class="col-span-1 md:col-span-1">' +
                    '    <label class="cu-label mb-1">{{ __('common.total_days') }}</label>' +
                    '    <input type="text" readonly class="custom-total cu-input bg-slate-50 text-slate-700" />' +
                    '</div>' +
                    '<div class="col-span-1 md:col-span-1 flex justify-end">' +
                    '    <button type="button" class="custom-remove cu-btn-danger !px-2 !py-1">{{ __('common.remove') }}</button>' +
                    '</div>';

                div.querySelector('.custom-start').addEventListener('change', function () { recalc(div); });
                div.querySelector('.custom-end').addEventListener('change', function () { recalc(div); });
                div.querySelector('.custom-half').addEventListener('change', function () {
                    var end = div.querySelector('.custom-end');
                    if (this.checked) {
                        end.disabled = true;
                        end.value = '';
                    } else {
                        end.disabled = false;
                    }
                    recalc(div);
                });
                div.querySelector('.custom-staff-input').addEventListener('change', function () {
                    var staff = resolveStaff(this.value);
                    div.querySelector('.custom-user-id').value = staff ? staff.id : '';
                });
                div.querySelector('.custom-remove').addEventListener('click', function () {
                    if (rowsContainer.children.length > 1) {
                        rowsContainer.removeChild(div);
                    }
                });

                rowsContainer.appendChild(div);
                recalc(div);
                return div;
            }

            document.getElementById('add-custom-row').addEventListener('click', buildRow);
            buildRow();

            document.getElementById('custom-import-form').addEventListener('submit', function (event) {
                event.preventDefault();

                var message = document.getElementById('custom-import-message');
                var rows = Array.prototype.slice.call(rowsContainer.children);
                var valid = true;
                var form = event.currentTarget;

                form.querySelectorAll('input[name^="rows["]').forEach(function (el) {
                    el.remove();
                });

                rows.forEach(function (row, i) {
                    var userInput = row.querySelector('.custom-user-id');
                    var nameInput = row.querySelector('.custom-staff-input');

                    if (!userInput.value) {
                        valid = false;
                        nameInput.classList.add('border-rose-400');
                        return;
                    }

                    ['user_id', 'leave_type_id', 'start_date', 'end_date', 'is_half_day'].forEach(function (field) {
                        var input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'rows[' + i + '][' + field + ']';
                        if (field === 'user_id') input.value = userInput.value;
                        if (field === 'leave_type_id') input.value = row.querySelector('.custom-leave-type').value;
                        if (field === 'start_date') input.value = row.querySelector('.custom-start').value;
                        if (field === 'end_date') input.value = row.querySelector('.custom-end').value;
                        if (field === 'is_half_day') input.value = row.querySelector('.custom-half').checked ? '1' : '0';
                        form.appendChild(input);
                    });
                });

                if (!valid) {
                    message.textContent = '{{ __('admin.custom_import_staff_required') }}';
                    return;
                }

                form.submit();
            });
        })();
    </script>
@endpush