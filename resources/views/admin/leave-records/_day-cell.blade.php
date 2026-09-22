@if ($leaveType === null)
    <span class="block w-full px-1 py-1 text-[11px] leading-4 text-slate-400">{{ $cell['label'] ?? '' }}</span>
@elseif (! empty($cell))
    <button type="button"
        class="day-cell block w-full rounded border border-slate-300 px-1 py-1 text-[11px] leading-4 whitespace-nowrap text-slate-700 hover:border-sky-500 hover:bg-sky-50"
        data-user="{{ $user->id }}"
        data-user-name="{{ $name }}"
        data-type="{{ $leaveType->id }}"
        data-type-name="{{ app()->getLocale() == 'my' ? ($leaveType->name_mm ?? $leaveType->name) : $leaveType->name }}"
        data-record="{{ $cell['id'] }}"
        data-start="{{ $cell['start'] }}"
        data-end="{{ $cell['end'] }}"
        data-half="{{ $cell['half'] ? '1' : '' }}">{{ $cell['label'] }}</button>
@else
    <button type="button"
        class="day-cell-add block w-full rounded border border-transparent px-1 py-1 text-[11px] leading-4 text-slate-300 hover:border-sky-500 hover:bg-sky-50 hover:text-sky-700"
        data-user="{{ $user->id }}"
        data-user-name="{{ $name }}"
        data-type="{{ $leaveType->id }}"
        data-type-name="{{ app()->getLocale() == 'my' ? ($leaveType->name_mm ?? $leaveType->name) : $leaveType->name }}">+</button>
@endif