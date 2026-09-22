@extends('layouts.app')

@section('title', __('admin.department_order_title'))

@section('content')
    <div class="space-y-6">
        <div class="cu-card-header">
            <div>
                <h2 class="cu-page-title">{{ __('admin.department_order_title') }}</h2>
                <p class="cu-muted mt-1">{{ __('admin.department_order_subtitle') }}</p>
            </div>
            <a href="{{ route('admin.departments.index') }}" class="cu-btn-secondary whitespace-nowrap">
                {{ __('common.back') }}
            </a>
        </div>

        @if (session('status'))
            <div class="cu-alert-success" role="status">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.departments.order.save') }}" id="department-order-form">
            @csrf

            <div class="cu-card cu-card-body">
                <ol class="dept-order-list">
                    @foreach($departments as $index => $department)
                        <li data-dept-id="{{ $department->id }}" class="flex items-center gap-3 border-b border-slate-100 py-2 last:border-b-0">
                            <span class="order-index w-6 shrink-0 text-center text-sm font-semibold text-slate-500">{{ $index + 1 }}</span>
                            <span class="min-w-0 flex-1 truncate text-sm text-slate-900">
                                {{ app()->getLocale() == 'my' ? ($department->name_mm ?? $department->name) : $department->name }}
                            </span>
                            <span class="flex shrink-0 items-center gap-1">
                                <select class="jump-select cu-select !px-2 !py-1 w-auto" aria-label="Jump to position">
                                    @for($pos = 1; $pos <= $departments->count(); $pos++)
                                        <option value="{{ $pos }}" {{ $pos === $index + 1 ? 'selected' : '' }}>{{ $pos }}</option>
                                    @endfor
                                </select>
                                <button type="button" class="move-btn move-up cu-btn-secondary !px-2 !py-1" aria-label="Move up">&uarr;</button>
                                <button type="button" class="move-btn move-down cu-btn-secondary !px-2 !py-1" aria-label="Move down">&darr;</button>
                            </span>
                        </li>
                    @endforeach
                </ol>
            </div>

            <div class="mt-6 flex items-center gap-3">
                <button type="submit" class="cu-btn-primary">{{ __('common.save') }}</button>
                <a href="{{ route('admin.departments.index') }}" class="cu-btn-secondary">{{ __('common.cancel') }}</a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            var list = document.querySelector('.dept-order-list');
            if (!list) return;

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

            document.getElementById('department-order-form').addEventListener('submit', function (event) {
                event.preventDefault();

                Array.prototype.forEach.call(list.children, function (li) {
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'dept_order[]';
                    input.value = li.dataset.deptId;
                    list.closest('form').appendChild(input);
                });

                event.currentTarget.submit();
            });
        })();
    </script>
@endpush