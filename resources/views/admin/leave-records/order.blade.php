@extends('layouts.app')

@section('title', __('admin.custom_staff_order'))

@section('content')
    <div class="space-y-6">
        <div class="cu-card-header">
            <div>
                <h2 class="cu-page-title">{{ __('admin.custom_staff_order') }}</h2>
                <p class="cu-muted mt-1">{{ __('admin.custom_staff_order_subtitle') }}</p>
            </div>
            <a href="{{ route('admin.leave-records') }}" class="cu-btn-secondary whitespace-nowrap">
                {{ __('common.back') }}
            </a>
        </div>

        @if (session('status'))
            <div class="cu-alert-success" role="status">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.leave-records.order.save') }}" id="staff-order-form">
            @csrf

            <div class="space-y-6">
                @foreach($departments as $block)
                    @php $department = $block['department']; $staff = $block['staff']; @endphp
                    <div class="cu-card cu-card-body">
                        <h3 class="mb-3 text-base font-semibold text-slate-800">
                            {{ app()->getLocale() == 'my' ? $department->name_mm ?? $department->name : $department->name }}
                        </h3>

                        @if(count($staff) > 0)
                            <ol class="staff-order-list" data-department="{{ $department->id }}">
                                @foreach($staff as $index => $user)
                                    <li data-user-id="{{ $user->id }}" class="flex items-center gap-3 border-b border-slate-100 py-2 last:border-b-0">
                                        <span class="order-index w-6 shrink-0 text-center text-sm font-semibold text-slate-500">{{ $index + 1 }}</span>
                                        <span class="min-w-0 flex-1 truncate text-sm text-slate-900">{{ $user->name_mm ?? $user->name }}</span>
                                        <span class="flex shrink-0 items-center gap-1">
                                            <select class="jump-select cu-select !px-2 !py-1 w-auto" aria-label="Jump to position">
                                                @for($pos = 1; $pos <= count($staff); $pos++)
                                                    <option value="{{ $pos }}" {{ $pos === $index + 1 ? 'selected' : '' }}>{{ $pos }}</option>
                                                @endfor
                                            </select>
                                            <button type="button" class="move-btn move-up cu-btn-secondary !px-2 !py-1" aria-label="Move up">&uarr;</button>
                                            <button type="button" class="move-btn move-down cu-btn-secondary !px-2 !py-1" aria-label="Move down">&darr;</button>
                                        </span>
                                    </li>
                                @endforeach
                            </ol>
                        @else
                            <p class="py-4 text-sm text-slate-500">{{ __('admin.leave_records_empty') }}</p>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-6 flex items-center gap-3">
                <button type="submit" class="cu-btn-primary">{{ __('common.save') }}</button>
                <a href="{{ route('admin.leave-records') }}" class="cu-btn-secondary">{{ __('common.cancel') }}</a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            var lists = document.querySelectorAll('.staff-order-list');

            lists.forEach(function (list) {
                function indexOf(li) {
                    return Array.prototype.indexOf.call(list.children, li);
                }

                function renumber() {
                    Array.prototype.forEach.call(list.children, function (li, i) {
                        li.querySelector('.order-index').textContent = i + 1;
                        var select = li.querySelector('.jump-select');
                        if (select) {
                            select.value = String(i + 1);
                        }
                    });
                }

                function jump(li, target) {
                    var i = indexOf(li);

                    if (target < 0) target = 0;
                    if (target > list.children.length - 1) target = list.children.length - 1;
                    if (target === i) return;

                    list.removeChild(li);

                    if (target === 0) {
                        list.insertBefore(li, list.firstChild);
                    } else if (target < i) {
                        list.insertBefore(li, list.children[target]);
                    } else {
                        list.insertBefore(li, list.children[target - 1].nextSibling);
                    }

                    renumber();
                }

                list.addEventListener('click', function (event) {
                    var button = event.target.closest('.move-btn');
                    if (!button) return;

                    var li = button.closest('li');
                    jump(li, indexOf(li) + (button.classList.contains('move-up') ? -1 : 1));
                });

                list.addEventListener('change', function (event) {
                    var select = event.target.closest('.jump-select');
                    if (!select) return;

                    jump(select.closest('li'), parseInt(select.value, 10) - 1);
                });
            });

            document.getElementById('staff-order-form').addEventListener('submit', function (event) {
                event.preventDefault();

                lists.forEach(function (list) {
                    Array.prototype.forEach.call(list.children, function (li) {
                        var input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'order[' + list.dataset.department + '][]';
                        input.value = li.dataset.userId;
                        list.closest('form').appendChild(input);
                    });
                });

                event.currentTarget.submit();
            });
        })();
    </script>
@endpush