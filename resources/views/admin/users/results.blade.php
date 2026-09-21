<div id="user-results">
    <div class="cu-table-wrap overflow-x-auto">
        <table class="cu-table">
                <thead>
                    <tr>
                        <th>{{ __('common.number') }}</th>
                        <th>
                            <a href="{{ route('admin.users.index', array_merge(request()->query(), ['sort' => 'name', 'direction' => $sort === 'name' && $direction === 'asc' ? 'desc' : 'asc'])) }}"
                               class="inline-flex items-center gap-1 hover:text-primary-600">
                                {{ __('common.name') }}
                            </a>
                        </th>
                        <th>{{ __('common.email') }}</th>
                        <th>
                            <a href="{{ route('admin.users.index', array_merge(request()->query(), ['sort' => 'role', 'direction' => $sort === 'role' && $direction === 'asc' ? 'desc' : 'asc'])) }}"
                               class="inline-flex items-center gap-1 hover:text-primary-600">
                                {{ __('common.role') }}
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('admin.users.index', array_merge(request()->query(), ['sort' => 'department', 'direction' => $sort === 'department' && $direction === 'asc' ? 'desc' : 'asc'])) }}"
                               class="inline-flex items-center gap-1 hover:text-primary-600">
                                {{ __('common.department') }}
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('admin.users.index', array_merge(request()->query(), ['sort' => 'position', 'direction' => $sort === 'position' && $direction === 'asc' ? 'desc' : 'asc'])) }}"
                               class="inline-flex items-center gap-1 hover:text-primary-600">
                                {{ __('common.position') }}
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('admin.users.index', array_merge(request()->query(), ['sort' => 'staff_id', 'direction' => $sort === 'staff_id' && $direction === 'asc' ? 'desc' : 'asc'])) }}"
                               class="inline-flex items-center gap-1 hover:text-primary-600">
                                {{ __('common.staff_id') }}
                            </a>
                        </th>
                        <th>{{ __('common.phone') }}</th>
                        <th>
                            <a href="{{ route('admin.users.index', array_merge(request()->query(), ['sort' => 'is_active', 'direction' => $sort === 'is_active' && $direction === 'asc' ? 'desc' : 'asc'])) }}"
                               class="inline-flex items-center gap-1 hover:text-primary-600">
                                {{ __('common.status') }}
                            </a>
                        </th>
                        <th>{{ __('common.actions') }}</th>
                    </tr>
                </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>{{ config('app.locale') == 'my' ? my_number($users->firstItem() + $loop->index) : $users->firstItem() + $loop->index }}</td>
                        <td class="primary">
                            <div class="flex items-center gap-2">
                                @if($user->profile_image)
                                    <img src="{{ asset('storage/' . $user->profile_image) }}" alt="{{ $user->name }}" class="w-8 h-8 rounded-full object-cover">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                @endif
                                {{ app()->getLocale() == 'my' ? $user->name_mm ?? $user->name : $user->name }}
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <span @class([
                                'cu-badge-admin' => $user->role === 'admin',
                                'cu-badge-info' => $user->role === 'department_head',
                                'cu-badge-neutral' => $user->role === 'staff',
                            ])>
                                {{ __('common.role.' . $user->role) }}
                            </span>
                        </td>
                        <td>{{ $user->department ? (app()->getLocale() == 'my' ? ($user->department->name_mm ?? $user->department->name) : $user->department->name) : __('common.n_a') }}</td>
                        <td>{{ app()->getLocale() == 'my' ? $user->position_mm ?? $user->position : $user->position ?? $user->position_mm ?? __('common.n_a') }}</td>
                        <td>{{ $user->staff_id ? $user->staff_id : __('common.n_a') }}</td>
                        <td>{{ my_phone($user->phone) }}</td>
                        <td>
                            <span @class([
                                'cu-badge-success' => $user->is_active,
                                'cu-badge-danger' => ! $user->is_active,
                            ])>
                                {{ $user->is_active ? __('common.staff.active') : __('common.staff.inactive') }}
                            </span>
                        </td>
                        <td class="flex items-center justify-center gap-2">
                            <a href="{{ route('admin.users.edit', $user) }}" class="cu-btn-secondary !px-3 !py-1.5 !rounded-full text-xs">{{ __('common.edit') }}</a>
                            <a href="{{ route('admin.staff.show', $user) }}" class="cu-btn-secondary !px-3 !py-1.5 !rounded-full text-xs">{{ __('common.view') }}</a>
                            @if($user->id !== auth()->id())
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="cu-btn-danger-nude !px-3 !py-1.5 text-xs"
                                            data-confirm="{{ __('admin.delete_this_user') }}">{{ __('common.delete') }}</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
</div>