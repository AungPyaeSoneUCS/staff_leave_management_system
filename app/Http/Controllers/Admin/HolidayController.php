<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        $query = Holiday::query();

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->when(app()->getLocale() === 'my', function ($q2) use ($search) {
                        $q2->orWhere('name_mm', 'like', "%{$search}%");
                    });
            });
        }

        if ($year = $request->query('year')) {
            $query->whereYear('date', $year);
        }

        if ($request->query('action') === 'clear') {
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');

            if ($startDate && $endDate) {
                Holiday::whereBetween('date', [$startDate, $endDate])->delete();

                return response()->json(['success' => true]);
            }
        }

        $holidays = $query->orderByDesc('date')->paginate(15);

        return view('admin.holidays.index', compact('holidays'));
    }

    public function calendar(Request $request)
    {
        $year = $request->query('year', now()->year);
        $month = $request->query('month', now()->month);

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = Carbon::create($year, $month, 1)->endOfMonth();

        $holidays = Holiday::whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get()
            ->keyBy(function ($holiday) {
                return Carbon::parse($holiday->date)->format('Y-m-d');
            });

        $today = Carbon::now('Asia/Yangon')->format('Y-m-d');

        return view('admin.holidays.calendar', compact('holidays', 'year', 'month', 'today'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_mm' => 'nullable|string|max:255',
            'date' => 'required|date|unique:holidays,date',
            'is_recurring' => 'boolean',
            'is_default' => 'boolean',
            'description' => 'nullable|string',
            'description_mm' => 'nullable|string',
            'replaced_holiday_id' => 'nullable|exists:holidays,id',
            'replacement_note' => 'nullable|string|max:500',
        ]);

        Holiday::create($validated);

        return redirect()->route('admin.holidays.index')
            ->with('success', __('flash.holiday_created'));
    }

    public function edit(Holiday $holiday)
    {
        return view('admin.holidays.edit', compact('holiday'));
    }

    public function update(Request $request, Holiday $holiday)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_mm' => 'nullable|string|max:255',
            'date' => 'required|date|unique:holidays,date,'.$holiday->id,
            'is_recurring' => 'boolean',
            'is_default' => 'boolean',
            'description' => 'nullable|string',
            'description_mm' => 'nullable|string',
            'replaced_holiday_id' => 'nullable|exists:holidays,id',
            'replacement_note' => 'nullable|string|max:500',
        ]);

        $holiday->update($validated);

        return redirect()->route('admin.holidays.index')
            ->with('success', __('flash.holiday_updated'));
    }

    public function destroy(Holiday $holiday)
    {
        $holiday->delete();

        return redirect()->route('admin.holidays.index')
            ->with('success', __('flash.holiday_deleted'));
    }

    public function storeAjax(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_mm' => 'nullable|string|max:255',
            'date' => 'required|date|unique:holidays,date',
            'is_recurring' => 'boolean',
            'is_default' => 'boolean',
            'description' => 'nullable|string',
            'description_mm' => 'nullable|string',
            'replaced_holiday_id' => 'nullable|exists:holidays,id',
            'replacement_note' => 'nullable|string|max:500',
        ]);

        $holiday = Holiday::create($validated);

        $locale = app()->getLocale();
        $name = $locale === 'my' ? ($holiday->name_mm ?? $holiday->name) : $holiday->name;

        return response()->json([
            'success' => true,
            'holiday' => [
                'id' => $holiday->id,
                'name' => $name,
                'date' => $holiday->date->format('Y-m-d'),
                'is_recurring' => $holiday->is_recurring,
                'is_default' => $holiday->is_default,
                'description' => $holiday->description,
                'replaced_holiday_id' => $holiday->replaced_holiday_id,
                'replacement_note' => $holiday->replacement_note,
            ],
        ]);
    }

    public function updateAjax(Request $request, Holiday $holiday)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_mm' => 'nullable|string|max:255',
            'date' => 'required|date|unique:holidays,date,'.$holiday->id,
            'is_recurring' => 'boolean',
            'is_default' => 'boolean',
            'description' => 'nullable|string',
            'description_mm' => 'nullable|string',
            'replaced_holiday_id' => 'nullable|exists:holidays,id',
            'replacement_note' => 'nullable|string|max:500',
        ]);

        $holiday->update($validated);

        $locale = app()->getLocale();
        $name = $locale === 'my' ? ($holiday->name_mm ?? $holiday->name) : $holiday->name;

        return response()->json([
            'success' => true,
            'holiday' => [
                'id' => $holiday->id,
                'name' => $name,
                'date' => $holiday->date->format('Y-m-d'),
                'is_recurring' => $holiday->is_recurring,
                'is_default' => $holiday->is_default,
                'description' => $holiday->description,
                'replaced_holiday_id' => $holiday->replaced_holiday_id,
                'replacement_note' => $holiday->replacement_note,
            ],
        ]);
    }

    public function destroyAjax(Holiday $holiday)
    {
        $holiday->delete();

        return response()->json(['success' => true]);
    }

    public function byDate(Request $request)
    {
        $date = $request->query('date');

        $holiday = Holiday::where('date', $date)->first();

        $locale = app()->getLocale();
        $name = $holiday ? ($locale === 'my' ? ($holiday->name_mm ?? $holiday->name) : $holiday->name) : null;

        return response()->json([
            'holiday' => $holiday ? [
                'id' => $holiday->id,
                'name' => $name,
                'date' => $holiday->date->format('Y-m-d'),
                'is_recurring' => $holiday->is_recurring,
                'is_default' => $holiday->is_default,
                'description' => $holiday->description,
                'replaced_holiday_id' => $holiday->replaced_holiday_id,
                'replacement_note' => $holiday->replacement_note,
            ] : null,
        ]);
    }

    public function clearMonth(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        Holiday::whereBetween('date', [$validated['start_date'], $validated['end_date']])->delete();

        return response()->json(['success' => true]);
    }
}
