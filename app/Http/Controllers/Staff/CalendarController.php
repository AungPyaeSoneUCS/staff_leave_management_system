<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = Carbon::create($year, $month, 1)->endOfMonth();

        $leaveRequests = $user->leaveRequests()
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('start_date', [$startOfMonth, $endOfMonth])
                    ->orWhereBetween('end_date', [$startOfMonth, $endOfMonth]);
            })
            ->with('leaveType')
            ->get()
            ->groupBy(function ($request) {
                return Carbon::parse($request->start_date)->format('Y-m-d');
            });

        $holidays = Holiday::whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get()
            ->keyBy(function ($holiday) {
                return Carbon::parse($holiday->date)->format('Y-m-d');
            });

        $calendar = [];
        for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
            $dateKey = $date->format('Y-m-d');
            $calendar[$dateKey] = [
                'date' => $date->copy(),
                'requests' => $leaveRequests->get($dateKey, collect()),
                'holiday' => $holidays->get($dateKey),
                'is_weekend' => $date->isWeekend(),
            ];
        }

        $today = Carbon::now('Asia/Yangon')->format('Y-m-d');

        return view('staff.calendar.index', compact('calendar', 'month', 'year', 'today'));
    }
}
