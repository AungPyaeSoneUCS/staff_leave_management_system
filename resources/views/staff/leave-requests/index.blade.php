@extends('layouts.app')

@section('title', __('staff.my_leave_requests'))

@section('content')
<div class="space-y-6">
    <div class="cu-card-header">
        <div>
            <h2 class="cu-page-title">{{ __('staff.my_leave_requests') }}</h2>
            <p class="cu-muted mt-1">{{ __('staff.track_and_manage') }}</p>
        </div>
        <a href="{{ route('staff.leave-requests.create') }}" class="cu-btn-primary">
            {{ __('staff.new_request') }}
        </a>
    </div>

    <div class="cu-card cu-card-body">
        <form method="GET" action="{{ route('staff.leave-requests.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
            <div>
                <label for="leave_type_id" class="block text-sm font-medium text-slate-700 mb-1">{{ __('common.select_leave_type') }}</label>
                <select id="leave_type_id" name="leave_type_id" class="cu-select">
                    <option value="">{{ __('common.select_leave_type') }}</option>
                    @foreach($leaveTypes as $leaveType)
                        <option value="{{ $leaveType->id }}" {{ request('leave_type_id') == $leaveType->id ? 'selected' : '' }}>
                            {{ app()->getLocale() == 'my' ? ($leaveType->name_mm ?? $leaveType->name) : $leaveType->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="start_date" class="block text-sm font-medium text-slate-700 mb-1">{{ __('common.start_date') }}</label>
                <input type="date" id="start_date" name="start_date" value="{{ request('start_date') }}" class="cu-input">
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium text-slate-700 mb-1">{{ __('common.end_date') }}</label>
                <input type="date" id="end_date" name="end_date" value="{{ request('end_date') }}" class="cu-input">
            </div>
            <div>
                <button type="submit" class="cu-btn-primary w-full">
                    {{ __('common.filter') }}
                </button>
            </div>
        </form>
    </div>

    @if($leaveRequests->isEmpty())
        <div class="cu-card cu-card-body text-center">
            <p class="text-slate-500 mb-4">{{ __('staff.no_leave_requests') }}</p>
            <a href="{{ route('staff.leave-requests.create') }}" class="cu-link">
                {{ __('staff.submit_first_request') }}
            </a>
        </div>
    @else
        <div class="cu-table-wrap overflow-x-auto">
            <table class="cu-table">
                <thead>
                    <tr>
                        <th>{{ __('common.number') }}</th>
                        <th>{{ __('common.leave_type') }}</th>
                        <th>{{ __('common.start_date') }}</th>
                        <th>{{ __('common.end_date') }}</th>
                        <th>{{ __('common.total_days') }}</th>
                        <th>{{ __('common.duty_exchange') }}</th>
                        <th>{{ __('common.attachment') }}</th>
                        <th>{{ __('common.status') }}</th>
                        <th>{{ __('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($leaveRequests as $request)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="primary">
                                {{ app()->getLocale() == 'my' ? ($request->leaveType->name_mm ?? $request->leaveType->name) : $request->leaveType->name }}
                            </td>
                            <td>
                                {{\App\Support\MyanmarDateFormatter::format($request->start_date, 'F d, Y')}}
                            </td>
                            <td>
                                {{ $request->end_date ? \App\Support\MyanmarDateFormatter::format($request->end_date, 'F d, Y') : __('common.unlimited') }}
                            </td>
                            <td>
                                {{ $request->leaveType->is_not_limited ? '-' : ($request->is_half_day ? __('common.half_day') : my_number($request->total_days)) }}
                            </td>
                            <td>
                                @if($request->duty_exchange_user_id && $request->dutyExchangeUser)
                                    {{ app()->getLocale() == 'my' ? ($request->dutyExchangeUser->name_mm ?? $request->dutyExchangeUser->name) : $request->dutyExchangeUser->name }}
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td>
                                @if($request->attachment_path)
                                    <span class="cu-badge-success">{{ __('common.yes') }}</span>
                                @else
                                    <span class="cu-badge-neutral">{{ __('common.no') }}</span>
                                @endif
                            </td>
                            <td>
                                <span @class([
                                    'cu-badge-success' => $request->status === 'approved',
                                    'cu-badge-warning' => $request->status === 'pending',
                                    'cu-badge-danger' => in_array($request->status, ['rejected', 'revoked']),
                                    'cu-badge-neutral' => ! in_array($request->status, ['approved', 'pending', 'rejected', 'revoked']),
                                ])>
                                    {{ __('common.' . $request->status) }}
                                </span>
                            </td>
                            <td class="flex items-center justify-center gap-2">
                                <a href="{{ route('staff.leave-requests.show', $request) }}" class="cu-btn-secondary !px-3 !py-1.5 !rounded-full text-xs">{{ __('common.view') }}</a>
                                @if($request->isPending())
                                    <form action="{{ route('staff.leave-requests.cancel', $request) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="cu-btn-danger !px-3 !py-1.5 !rounded-full text-xs" data-confirm="{{ __('staff.cancel_this_request') }}">{{ __('common.cancel') }}</button>
                                    </form>
                                    <form action="{{ route('staff.leave-requests.destroy', $request) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="cu-btn-danger !px-3 !py-1.5 !rounded-full text-xs" data-confirm="{{ __('staff.delete_this_request') }}">{{ __('common.delete') }}</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $leaveRequests->links() }}
        </div>
    @endif
</div>
@endsection
