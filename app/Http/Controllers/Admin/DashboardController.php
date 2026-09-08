<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use App\Services\AnalyticsService;
use App\Support\MyanmarDateFormatter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Layout;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title as ChartTitle;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class DashboardController extends Controller
{
    public function __construct(
        private AnalyticsService $analyticsService
    ) {}

    private function localizedName(string $name, ?string $nameMm): string
    {
        return app()->getLocale() === 'my' && ! empty($nameMm) ? $nameMm : $name;
    }

    private function localizeBalanceExportData(array $data): array
    {
        return array_map(function ($item) {
            return [
                'staff_name' => $item['staff_name'] ?? '—',
                'staff_id' => $item['staff_id'] ?? '—',
                'department' => $item['department'] ?? '—',
                'leave_type' => $item['leave_type'] ?? '—',
                'allocated_days' => $item['is_not_limited'] ? '-' : my_number($item['allocated_days'] ?? 0),
                'used_days' => $item['is_not_limited'] ? '-' : my_number($item['used_days'] ?? 0),
                'remaining_days' => $item['is_not_limited'] ? '-' : my_number($item['remaining_days'] ?? 0),
                'is_not_limited' => $item['is_not_limited'] ?? false,
                'profile_image' => $item['profile_image'] ?? null,
            ];
        }, $data);
    }

    public function index(Request $request)
    {
        $statistics = $this->analyticsService->getDashboardStatistics($request->user());
        $leaveByType = $this->analyticsService->getLeaveStatisticsByType();
        $departmentStats = $this->analyticsService->getDepartmentLeaveStatistics();
        $recentRequests = LeaveRequest::with('user', 'leaveType', 'reviewer', 'hr', 'dutyExchangeUser')
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact('statistics', 'leaveByType', 'departmentStats', 'recentRequests'));
    }

    public function balanceReport()
    {
        $departments = Department::get();
        $leaveTypes = LeaveType::where('is_active', true)->get();
        $currentYear = now()->year;

        return view('admin.reports.balance', compact('departments', 'leaveTypes', 'currentYear'));
    }

    public function leaveSummaryReport()
    {
        $departments = Department::get();
        $leaveTypes = LeaveType::where('is_active', true)->get();
        $currentYear = now()->year;

        return view('admin.reports.leave-summary', compact('departments', 'leaveTypes', 'currentYear'));
    }

    public function leaveTypeReport()
    {
        $departments = Department::get();
        $leaveTypes = LeaveType::where('is_active', true)->get();
        $currentYear = now()->year;

        return view('admin.reports.leave-type', compact('departments', 'leaveTypes', 'currentYear'));
    }

    public function departmentReport()
    {
        $departments = Department::get();
        $leaveTypes = LeaveType::where('is_active', true)->get();
        $currentYear = now()->year;

        return view('admin.reports.department', compact('departments', 'leaveTypes', 'currentYear'));
    }

    public function dailyReport()
    {
        return view('admin.reports.daily');
    }

    public function getDailyReportData(Request $request)
    {
        $filters = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        $today = now()->format('Y-m-d');
        $startDate = $filters['start_date'] ?? $today;
        $endDate = $filters['end_date'] ?? $today;

        $query = LeaveRequest::query()
            ->with(['user', 'leaveType', 'user.department', 'reviewer', 'dutyExchangeUser'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->orderByDesc('created_at')
            ->get();

        $data = $query->map(function ($item) {
            return [
                'staff_name' => app()->getLocale() == 'my' ? ($item->user->name_mm ?? $item->user->name) : $item->user->name,
                'staff_id' => $item->user->staff_id ?? '—',
                'department' => $item->user->department ? (app()->getLocale() == 'my' ? ($item->user->department->name_mm ?? $item->user->department->name) : $item->user->department->name) : __('common.n_a'),
                'leave_type' => app()->getLocale() == 'my' ? ($item->leaveType->name_mm ?? $item->leaveType->name) : $item->leaveType->name,
                'start_date' => MyanmarDateFormatter::format($item->start_date, 'F d, Y'),
                'end_date' => $item->end_date ? MyanmarDateFormatter::format($item->end_date, 'F d, Y') : '—',
                'total_days' => my_number($item->total_days),
                'is_not_limited' => $item->leaveType->is_not_limited,
                'status' => $item->status,
                'duty_exchange' => $item->duty_exchange_user_id && $item->dutyExchangeUser ? (app()->getLocale() == 'my' ? ($item->dutyExchangeUser->name_mm ?? $item->dutyExchangeUser->name) : $item->dutyExchangeUser->name) : '—',
            ];
        })->values()->toArray();

        return response()->json([
            'table' => $data,
            'chart' => [
                'labels' => [],
                'values' => [],
            ],
        ]);
    }

    public function exportPdf(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(['leave_summary', 'balance', 'leave_type', 'department', 'daily'])],
            'department_id' => ['nullable', 'exists:departments,id'],
            'staff_name' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'leave_type_id' => ['nullable', 'exists:leave_types,id'],
            'date_filter' => ['nullable', 'in:leave_period,created_at,reviewed_at'],
        ]);

        $filters = Arr::only($validated, ['department_id', 'staff_name', 'start_date', 'end_date', 'year', 'leave_type_id', 'date_filter']);

        $filterSummary = $this->buildFilterSummary($filters);

        $title = match ($validated['type']) {
            'leave_summary' => __('admin.leave_summary_report'),
            'balance' => __('admin.balance_report'),
            'leave_type' => __('admin.leave_type_report'),
            'department' => __('admin.department_report'),
            'daily' => __('admin.daily_report'),
        };

        if ($validated['type'] === 'leave_summary') {
            $payload = $this->getLeaveSummaryData($request)->getData(true);
            $data = $payload['table'] ?? [];
            $chart = $payload['chart'] ?? [];
        } elseif ($validated['type'] === 'leave_type') {
            $payload = $this->getLeaveTypeData($request)->getData(true);
            $data = $payload['table'] ?? [];
            $chart = $payload['chart'] ?? [];
        } elseif ($validated['type'] === 'department') {
            $payload = $this->getDepartmentData($request)->getData(true);
            $data = $payload['table'] ?? [];
            $chart = $payload['chart'] ?? [];
        } elseif ($validated['type'] === 'daily') {
            $payload = $this->getDailyReportData($request)->getData(true);
            $data = $payload['table'] ?? [];
            $chart = $payload['chart'] ?? [];
        } else {
            $rawData = $this->analyticsService->getLeaveBalances($filters);
            $chart = $this->buildBalancePerStaffChart($rawData);
            $data = $this->localizeBalanceExportData($rawData);
        }

        $html = view('admin.reports.pdf', [
            'type' => $validated['type'],
            'data' => $data,
            'title' => $title,
            'filterSummary' => $filterSummary,
            'chart' => $chart,
        ])->render();

        $this->clearMpdfFontCache();

        $defaultConfig = (new ConfigVariables)->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new FontVariables)->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new Mpdf([
            'fontDir' => array_merge($fontDirs, [
                base_path('resources/fonts/Noto_Sans_Myanmar/static'),
            ]),
            'fontdata' => $fontData + [
                'notosansmyanmar' => [
                    'R' => 'NotoSansMyanmar-Regular.ttf',
                    'B' => 'NotoSansMyanmar-Bold.ttf',
                    'I' => 'NotoSansMyanmar-Regular.ttf',
                    'BI' => 'NotoSansMyanmar-Bold.ttf',
                ],
            ],
            'default_font' => 'notosansmyanmar',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'tempDir' => $this->mpdfTempDir(),
        ]);

        $mpdf->WriteHTML($html);

        $filename = $validated['type'].'-report-'.now()->format('Y-m-d').'.pdf';

        $content = $mpdf->Output('', Destination::STRING_RETURN);

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Length' => strlen($content),
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function exportXlsx(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(['leave_summary', 'balance', 'leave_type', 'department', 'daily'])],
            'department_id' => ['nullable', 'exists:departments,id'],
            'staff_name' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'leave_type_id' => ['nullable', 'exists:leave_types,id'],
            'date_filter' => ['nullable', 'in:leave_period,created_at,reviewed_at'],
        ]);

        $filters = Arr::only($validated, ['department_id', 'staff_name', 'start_date', 'end_date', 'year', 'leave_type_id', 'date_filter']);
        $filterSummary = $this->buildFilterSummary($filters);

        $title = match ($validated['type']) {
            'leave_summary' => __('admin.leave_summary_report'),
            'balance' => __('admin.balance_report'),
            'leave_type' => __('admin.leave_type_report'),
            'department' => __('admin.department_report'),
            'daily' => __('admin.daily_report'),
        };

        if ($validated['type'] === 'leave_summary') {
            $payload = $this->getLeaveSummaryData($request)->getData(true);
            $data = $payload['table'] ?? [];
            $chart = $payload['chart'] ?? [];
        } elseif ($validated['type'] === 'leave_type') {
            $payload = $this->getLeaveTypeData($request)->getData(true);
            $data = $payload['table'] ?? [];
            $chart = $payload['chart'] ?? [];
        } elseif ($validated['type'] === 'department') {
            $payload = $this->getDepartmentData($request)->getData(true);
            $data = $payload['table'] ?? [];
            $chart = $payload['chart'] ?? [];
        } elseif ($validated['type'] === 'daily') {
            $payload = $this->getDailyReportData($request)->getData(true);
            $data = $payload['table'] ?? [];
            $chart = $payload['chart'] ?? [];
        } else {
            $rawData = $this->analyticsService->getLeaveBalances($filters);
            $chart = $this->buildBalancePerStaffChart($rawData);
            $data = $this->localizeBalanceExportData($rawData);
        }

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheetTitle = mb_substr($title, 0, 31);
        $sheet->setTitle($sheetTitle);

        $rowNum = 1;
        $lastColumn = match ($validated['type']) {
            'leave_summary' => 'J',
            'balance' => 'G',
            'leave_type' => 'C',
            'department' => 'C',
            'daily' => 'H',
        };
        $sheet->mergeCells('A'.$rowNum.':'.$lastColumn.$rowNum);
        $sheet->setCellValue('A'.$rowNum, $title);
        $sheet->getStyle('A'.$rowNum)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A'.$rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $rowNum++;

        if (! empty($filterSummary)) {
            $sheet->setCellValue('A'.$rowNum, __('admin.generated').': '.now()->format('Y-m-d H:i'));
            $rowNum++;
            foreach ($filterSummary as $label => $value) {
                $sheet->setCellValue('A'.$rowNum, $label.': '.$value);
                $rowNum++;
            }
            $rowNum++;
        }

        if ($validated['type'] === 'leave_summary' && ! empty($data)) {
            $headers = [__('common.number'), __('common.name'), __('common.staff_id'), __('common.department'), __('common.leave_type'), __('common.start_date'), __('common.end_date'), __('common.total_days'), __('common.status'), __('common.duty_exchange')];
            $sheet->fromArray($headers, null, 'A'.$rowNum);
            $sheet->getStyle('A'.$rowNum)->getFont()->setBold(true);
            $rowNum++;
            foreach ($data as $index => $item) {
                $sheet->setCellValue('A'.$rowNum, config('app.locale') == 'my' ? my_number($index + 1) : $index + 1);
                $sheet->setCellValue('B'.$rowNum, $item['staff_name']);
                $sheet->setCellValue('C'.$rowNum, $item['staff_id']);
                $sheet->setCellValue('D'.$rowNum, $item['department']);
                $sheet->setCellValue('E'.$rowNum, $item['leave_type']);
                $sheet->setCellValue('F'.$rowNum, $item['start_date']);
                $sheet->setCellValue('G'.$rowNum, $item['end_date']);
                $sheet->setCellValue('H'.$rowNum, $item['is_not_limited'] ? '-' : $item['total_days']);
                $sheet->setCellValue('I'.$rowNum, __('common.'.$item['status']));
                $sheet->setCellValue('J'.$rowNum, $item['duty_exchange']);
                $rowNum++;
            }
            if (! empty($chart['labels'])) {
                $this->addXlsxChart($spreadsheet, $sheet, $chart, $rowNum, count($headers));
            }
        } elseif ($validated['type'] === 'balance' && ! empty($rawData)) {
            $headers = [__('common.number'), __('common.name'), __('common.department'), __('common.leave_type'), __('common.allocated_days'), __('common.used_days'), __('common.remaining_days')];
            $sheet->fromArray($headers, null, 'A'.$rowNum);
            $sheet->getStyle('A'.$rowNum)->getFont()->setBold(true);
            $rowNum++;
            $grouped = [];
            foreach ($rawData as $item) {
                $name = $item['staff_name'] ?? '—';
                if (! isset($grouped[$name])) {
                    $grouped[$name] = [
                        'staff_name' => $name,
                        'department' => $item['department'],
                        'leave_type' => $item['leave_type'] ?? '',
                        'allocated_days' => 0,
                        'used_days' => 0,
                        'remaining_days' => 0,
                        'is_not_limited' => $item['is_not_limited'] ?? false,
                    ];
                }
                if (! ($item['is_not_limited'] ?? false)) {
                    $grouped[$name]['allocated_days'] += (float) ($item['allocated_days'] ?? 0);
                    $grouped[$name]['used_days'] += (float) ($item['used_days'] ?? 0);
                    $grouped[$name]['remaining_days'] += (float) ($item['remaining_days'] ?? 0);
                }
            }
            foreach (array_values($grouped) as $index => $item) {
                $sheet->setCellValue('A'.$rowNum, config('app.locale') == 'my' ? my_number($index + 1) : $index + 1);
                $sheet->setCellValue('B'.$rowNum, $item['staff_name']);
                $sheet->setCellValue('C'.$rowNum, $item['department']);
                $sheet->setCellValue('D'.$rowNum, $item['leave_type']);
                $sheet->setCellValue('E'.$rowNum, $item['is_not_limited'] ? '-' : my_number($item['allocated_days']));
                $sheet->setCellValue('F'.$rowNum, $item['is_not_limited'] ? '-' : my_number($item['used_days']));
                $sheet->setCellValue('G'.$rowNum, $item['is_not_limited'] ? '-' : my_number($item['remaining_days']));
                $rowNum++;
            }
            if (! empty($chart['labels'])) {
                $this->addBalanceXlsxChart($spreadsheet, $sheet, $chart, $rowNum, count($headers));
            }
        } elseif ($validated['type'] === 'leave_type' && ! empty($data)) {
            $headers = [__('common.number'), __('common.leave_type'), __('common.staff_count')];
            $sheet->fromArray($headers, null, 'A'.$rowNum);
            $sheet->getStyle('A'.$rowNum)->getFont()->setBold(true);
            $rowNum++;
            foreach ($data as $index => $item) {
                $sheet->setCellValue('A'.$rowNum, config('app.locale') == 'my' ? my_number($index + 1) : $index + 1);
                $sheet->setCellValue('B'.$rowNum, $item['leave_type']);
                $sheet->setCellValue('C'.$rowNum, $item['staff_count'] ?? $item['total_days'] ?? 0);
                $rowNum++;
            }
            if (! empty($chart['labels'])) {
                $this->addXlsxChart($spreadsheet, $sheet, $chart, $rowNum, count($headers));
            }
        } elseif ($validated['type'] === 'department' && ! empty($data)) {
            $headers = [__('common.number'), __('common.department'), __('common.staff_count'), __('common.total_days')];
            $sheet->fromArray($headers, null, 'A'.$rowNum);
            $sheet->getStyle('A'.$rowNum)->getFont()->setBold(true);
            $rowNum++;
            foreach ($data as $index => $item) {
                $sheet->setCellValue('A'.$rowNum, config('app.locale') == 'my' ? my_number($index + 1) : $index + 1);
                $sheet->setCellValue('B'.$rowNum, $item['department']);
                $sheet->setCellValue('C'.$rowNum, $item['staff_count'] ?? $item['staff_count_raw'] ?? 0);
                $sheet->setCellValue('D'.$rowNum, $item['is_not_limited'] ? '-' : $item['total_days']);
                $rowNum++;
            }
            if (! empty($chart['labels'])) {
                $this->addXlsxChart($spreadsheet, $sheet, $chart, $rowNum, count($headers));
            }
        } elseif ($validated['type'] === 'daily' && ! empty($data)) {
            $headers = [__('common.number'), __('common.name'), __('common.staff_id'), __('common.department'), __('common.leave_type'), __('common.total_days'), __('common.status'), __('common.duty_exchange')];
            $sheet->fromArray($headers, null, 'A'.$rowNum);
            $sheet->getStyle('A'.$rowNum)->getFont()->setBold(true);
            $rowNum++;
            foreach ($data as $index => $item) {
                $sheet->setCellValue('A'.$rowNum, config('app.locale') == 'my' ? my_number($index + 1) : $index + 1);
                $sheet->setCellValue('B'.$rowNum, $item['staff_name']);
                $sheet->setCellValue('C'.$rowNum, $item['staff_id']);
                $sheet->setCellValue('D'.$rowNum, $item['department']);
                $sheet->setCellValue('E'.$rowNum, $item['leave_type']);
                $sheet->setCellValue('F'.$rowNum, $item['is_not_limited'] ? '-' : $item['total_days']);
                $sheet->setCellValue('G'.$rowNum, __('common.'.$item['status']));
                $sheet->setCellValue('H'.$rowNum, $item['duty_exchange']);
                $rowNum++;
            }
        }

        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = $validated['type'].'-report-'.now()->format('Y-m-d').'.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->setIncludeCharts(true);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function addXlsxChart(Spreadsheet $spreadsheet, Worksheet $sheet, array $chart, int $startRow, int $colCount): void
    {
        if (empty($chart['labels']) || empty($chart['values'])) {
            return;
        }

        $labels = $chart['labels'];
        $values = array_map('floatval', $chart['values']);
        $count = count($labels);
        if ($count === 0) {
            return;
        }

        $sheetTitle = $sheet->getTitle();
        $escapedSheetTitle = str_replace("'", "''", $sheetTitle);

        foreach ($labels as $i => $label) {
            $row = $i + 1;
            $sheet->setCellValue('L'.$row, $label);
            $sheet->setCellValue('M'.$row, $values[$i]);
        }

        $labelsData = new DataSeriesValues(
            DataSeriesValues::DATASERIES_TYPE_STRING,
            "'{$escapedSheetTitle}'!\$L\$1:\$L\${$count}",
            null,
            $count
        );
        $valuesData = new DataSeriesValues(
            DataSeriesValues::DATASERIES_TYPE_NUMBER,
            "'{$escapedSheetTitle}'!\$M\$1:\$M\${$count}",
            null,
            $count
        );

        $series = new DataSeries(
            DataSeries::TYPE_BARCHART,
            DataSeries::GROUPING_CLUSTERED,
            [0],
            [0 => new DataSeriesValues],
            [$labelsData],
            [$valuesData]
        );
        $series->setPlotDirection(DataSeries::DIRECTION_COL);

        $layout = new Layout;
        $layout->setShowVal(true);

        $plotArea = new PlotArea($layout, [$series]);
        $chartObj = new Chart('chart', new ChartTitle($sheetTitle), null, $plotArea);
        $chartObj->setTopLeftPosition('L'.($startRow + 1));
        $chartObj->setBottomRightPosition('T'.($startRow + 16));

        $sheet->addChart($chartObj);
    }

    private function addBalanceXlsxChart(Spreadsheet $spreadsheet, Worksheet $sheet, array $chart, int $startRow, int $colCount): void
    {
        if (empty($chart['labels']) || empty($chart['used']) || empty($chart['remaining'])) {
            return;
        }

        $labels = $chart['labels'];
        $used = array_map('floatval', $chart['used']);
        $remaining = array_map('floatval', $chart['remaining']);
        $count = count($labels);
        if ($count === 0) {
            return;
        }

        $sheetTitle = $sheet->getTitle();
        $escapedSheetTitle = str_replace("'", "''", $sheetTitle);

        foreach ($labels as $i => $label) {
            $row = $i + 1;
            $sheet->setCellValue('L'.$row, $label);
            $sheet->setCellValue('M'.$row, $used[$i]);
            $sheet->setCellValue('N'.$row, $remaining[$i]);
        }

        $labelsData = new DataSeriesValues(
            DataSeriesValues::DATASERIES_TYPE_STRING,
            "'{$escapedSheetTitle}'!\$L\$1:\$L\${$count}",
            null,
            $count
        );
        $usedData = new DataSeriesValues(
            DataSeriesValues::DATASERIES_TYPE_NUMBER,
            "'{$escapedSheetTitle}'!\$M\$1:\$M\${$count}",
            null,
            $count
        );
        $remainingData = new DataSeriesValues(
            DataSeriesValues::DATASERIES_TYPE_NUMBER,
            "'{$escapedSheetTitle}'!\$N\$1:\$N\${$count}",
            null,
            $count
        );

        $series = new DataSeries(
            DataSeries::TYPE_BARCHART,
            DataSeries::GROUPING_CLUSTERED,
            [0, 1],
            [0 => new DataSeriesValues, 1 => new DataSeriesValues],
            [$labelsData, $labelsData],
            [$usedData, $remainingData]
        );
        $series->setPlotDirection(DataSeries::DIRECTION_COL);

        $layout = new Layout;
        $layout->setShowVal(true);

        $plotArea = new PlotArea($layout, [$series]);
        $chartObj = new Chart('chart', new ChartTitle($sheetTitle), null, $plotArea);
        $chartObj->setTopLeftPosition('L'.($startRow + 1));
        $chartObj->setBottomRightPosition('T'.($startRow + 16));

        $sheet->addChart($chartObj);
    }

    public function getLeaveSummaryData(Request $request)
    {
        $filters = $request->validate([
            'department_id' => ['nullable', 'exists:departments,id'],
            'staff_name' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:approved,rejected,cancelled,revoked'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'date_filter' => ['nullable', 'in:leave_period,created_at,reviewed_at'],
        ]);
        $dateFilter = $filters['date_filter'] ?? 'leave_period';

        $query = LeaveRequest::query()
            ->with(['user.department', 'leaveType', 'reviewer', 'dutyExchangeUser'])
            ->where('status', '!=', 'pending')
            ->when(! empty($filters['department_id']), function ($query) use ($filters) {
                $query->whereHas('user', function ($q) use ($filters) {
                    $q->where('department_id', $filters['department_id']);
                });
            })
            ->when(! empty($filters['staff_name']), function ($query) use ($filters) {
                $query->whereHas('user', function ($q) use ($filters) {
                    $q->where(function ($sub) use ($filters) {
                        $sub->where('name', 'like', '%'.$filters['staff_name'].'%')
                            ->when(app()->getLocale() === 'my', function ($q2) use ($filters) {
                                $q2->orWhere('name_mm', 'like', '%'.$filters['staff_name'].'%');
                            });
                    });
                });
            })
            ->when(! empty($filters['status']), function ($query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->when(! empty($filters['start_date']), function ($query) use ($filters, $dateFilter) {
                $column = match ($dateFilter) {
                    'created_at' => 'created_at',
                    'reviewed_at' => 'reviewed_at',
                    default => 'start_date',
                };
                $query->whereDate($column, '>=', $filters['start_date']);
            })
            ->when(! empty($filters['end_date']), function ($query) use ($filters, $dateFilter) {
                $column = match ($dateFilter) {
                    'created_at' => 'created_at',
                    'reviewed_at' => 'reviewed_at',
                    default => 'end_date',
                };
                $query->whereDate($column, '<=', $filters['end_date']);
            })
            ->orderByDesc(match ($dateFilter) {
                'created_at' => 'created_at',
                'reviewed_at' => 'reviewed_at',
                default => 'start_date',
            })
            ->get();

        $data = $query->map(function ($item) {
            return [
                'staff_name' => $this->localizedName($item->user->name, $item->user->name_mm),
                'staff_id' => $item->user->staff_id ?? '—',
                'department' => $item->user->department ? $this->localizedName($item->user->department->name, $item->user->department->name_mm) : '—',
                'leave_type' => $this->localizedName($item->leaveType->name, $item->leaveType->name_mm),
                'start_date' => MyanmarDateFormatter::format($item->start_date, 'F d, Y'),
                'end_date' => $item->end_date ? MyanmarDateFormatter::format($item->end_date, 'F d, Y') : '—',
                'total_days' => my_number($item->total_days),
                'is_not_limited' => $item->leaveType->is_not_limited,
                'status' => $item->status,
                'reviewer' => $item->reviewer ? $this->localizedName($item->reviewer->name, $item->reviewer->name_mm) : '—',
                'reviewed_at' => $item->reviewed_at ? MyanmarDateFormatter::format($item->reviewed_at, 'F d, Y') : '—',
                'profile_image' => $item->user->profile_image,
                'duty_exchange' => $item->dutyExchangeUser ? $this->localizedName($item->dutyExchangeUser->name, $item->dutyExchangeUser->name_mm) : '—',
            ];
        })->values()->toArray();

        $allLeaveTypes = LeaveType::where('is_active', true)->get()->mapWithKeys(function ($lt) {
            $localizedName = $this->localizedName($lt->name, $lt->name_mm);

            return [$lt->name => $localizedName];
        });
        $actualData = $query->groupBy('leaveType.name')->map(fn ($items) => $items->sum('total_days'));

        $chartLabels = $allLeaveTypes->values()->toArray();
        $chartValues = $allLeaveTypes->keys()->map(fn ($englishName) => $actualData[$englishName] ?? 0)->values()->toArray();

        return response()->json([
            'table' => $data,
            'chart' => [
                'labels' => $chartLabels,
                'values' => $chartValues,
            ],
        ]);
    }

    public function getLeaveTypeData(Request $request)
    {
        $filters = $request->validate([
            'department_id' => ['nullable', 'exists:departments,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'date_filter' => ['nullable', 'in:created_at,reviewed_at'],
        ]);
        $dateFilter = $filters['date_filter'] ?? 'created_at';

        /** @var Builder $queryBuilder */
        $queryBuilder = LeaveRequest::query()
            ->with(['user.department', 'leaveType'])
            ->when(! empty($filters['department_id']), function ($query) use ($filters) {
                $query->whereHas('user', function ($q) use ($filters) {
                    $q->where('department_id', $filters['department_id']);
                });
            })
            ->when(! empty($filters['start_date']), function ($query) use ($filters, $dateFilter) {
                $query->whereDate($dateFilter, '>=', $filters['start_date']);
            })
            ->when(! empty($filters['end_date']), function ($query) use ($filters, $dateFilter) {
                $query->whereDate($dateFilter, '<=', $filters['end_date']);
            })
            ->orderByDesc($dateFilter);

        /** @var Collection<int, LeaveRequest> $leaveRequests */
        $leaveRequests = $queryBuilder->get();

        $allLeaveTypes = LeaveType::where('is_active', true)->get()->mapWithKeys(function ($lt) {
            $localizedName = $this->localizedName($lt->name, $lt->name_mm);

            return [$lt->name => [
                'leave_type' => $localizedName,
                'staff_count' => 0,
                'staff_count_raw' => 0,
                'is_not_limited' => $lt->is_not_limited,
            ]];
        });

        $actualData = $leaveRequests->groupBy(fn ($item) => $item->leaveType->name)
            ->map(fn ($items) => $items->unique('user_id')->count());

        $rawTotals = [];
        foreach ($allLeaveTypes as $name => $info) {
            $rawTotals[$name] = (int) ($actualData[$name] ?? 0);
            $info['staff_count'] = my_number($rawTotals[$name]);
            $info['staff_count_raw'] = $rawTotals[$name];
            $allLeaveTypes[$name] = $info;
        }

        $table = $allLeaveTypes->values()->toArray();
        $chartLabels = $allLeaveTypes->pluck('leave_type')->values()->toArray();
        $chartValues = array_values($rawTotals);
        $colors = $this->chartPalette(count($chartLabels));

        return response()->json([
            'table' => $table,
            'chart' => [
                'labels' => $chartLabels,
                'values' => $chartValues,
                'colors' => $colors,
            ],
        ]);
    }

    public function getDepartmentData(Request $request)
    {
        $filters = $request->validate([
            'department_id' => ['nullable', 'exists:departments,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'date_filter' => ['nullable', 'in:created_at,reviewed_at'],
        ]);
        $dateFilter = $filters['date_filter'] ?? 'created_at';

        $query = LeaveRequest::query()
            ->with(['user.department', 'leaveType'])
            ->when(! empty($filters['department_id']), function ($query) use ($filters) {
                $query->whereHas('user', function ($q) use ($filters) {
                    $q->where('department_id', $filters['department_id']);
                });
            })
            ->when(! empty($filters['start_date']), function ($query) use ($filters, $dateFilter) {
                $query->whereDate($dateFilter, '>=', $filters['start_date']);
            })
            ->when(! empty($filters['end_date']), function ($query) use ($filters, $dateFilter) {
                $query->whereDate($dateFilter, '<=', $filters['end_date']);
            })
            ->orderByDesc($dateFilter)
            ->get();

        $allDepartments = Department::get()->mapWithKeys(function ($dept) {
            $localizedName = $this->localizedName($dept->name, $dept->name_mm);

            return [$dept->id => [
                'department' => $localizedName,
                'total_days' => 0,
                'total_days_raw' => 0,
                'staff_count' => 0,
                'staff_count_raw' => 0,
                'is_not_limited' => false,
            ]];
        });

        $grouped = $query->groupBy('user.department.id');

        $dayTotals = $grouped->map(function ($items) {
            $total = 0;
            foreach ($items as $item) {
                $total += $item->leaveType->is_not_limited ? 0 : $item->total_days;
            }

            return $total;
        });

        $staffTotals = $grouped->map(fn ($items) => $items->unique('user_id')->count());

        $rawTotals = [];
        $rawStaffTotals = [];
        foreach ($allDepartments as $deptId => $info) {
            $rawTotals[$deptId] = (float) ($dayTotals[$deptId] ?? 0);
            $rawStaffTotals[$deptId] = (int) ($staffTotals[$deptId] ?? 0);
            $info['total_days'] = my_number($rawTotals[$deptId]);
            $info['total_days_raw'] = $rawTotals[$deptId];
            $info['staff_count'] = my_number($rawStaffTotals[$deptId]);
            $info['staff_count_raw'] = $rawStaffTotals[$deptId];
            $allDepartments[$deptId] = $info;
        }

        $table = $allDepartments->values()->toArray();
        $chartLabels = $allDepartments->pluck('department')->values()->toArray();
        $chartValues = array_values($rawTotals);
        $chartStaffValues = array_values($rawStaffTotals);
        $colors = $this->chartPalette(count($chartLabels));

        return response()->json([
            'table' => $table,
            'chart' => [
                'labels' => $chartLabels,
                'values' => $chartValues,
                'staff_values' => $chartStaffValues,
                'colors' => $colors,
            ],
        ]);
    }

    public function getBalanceData(Request $request)
    {
        $filters = $request->validate([
            'department_id' => ['nullable', 'exists:departments,id'],
            'staff_name' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'leave_type_id' => ['nullable', 'exists:leave_types,id'],
        ]);

        $year = $filters['year'] ?? (int) now()->year;

        $staffQuery = User::where(function ($q) {
                $q->where('role', 'staff')->orWhere('role', 'department_head');
            })
            ->with('department');

        if (! empty($filters['department_id'])) {
            $staffQuery->where('department_id', $filters['department_id']);
        }

        if (! empty($filters['staff_name'])) {
            $staffQuery->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%'.$filters['staff_name'].'%')
                    ->when(app()->getLocale() === 'my', function ($q2) use ($filters) {
                        $q2->orWhere('name_mm', 'like', '%'.$filters['staff_name'].'%');
                    });
            });
        }

        $staff = $staffQuery->orderBy('name')->get();

        $leaveTypeQuery = LeaveType::where('is_active', true);
        if (! empty($filters['leave_type_id'])) {
            $leaveTypeQuery->where('id', $filters['leave_type_id']);
        }
        $leaveTypes = $leaveTypeQuery->get();

        $existingBalances = LeaveBalance::where('year', $year)
            ->whereIn('user_id', $staff->pluck('id'))
            ->whereIn('leave_type_id', $leaveTypes->pluck('id'))
            ->get()
            ->keyBy(fn ($b) => $b->user_id.'_'.$b->leave_type_id);

        $data = [];
        foreach ($staff as $user) {
            foreach ($leaveTypes as $leaveType) {
                $balance = $existingBalances->get($user->id.'_'.$leaveType->id);

                if ($leaveType->track_on_use && (! $balance || $balance->used_days == 0)) {
                    continue;
                }

                $allocated = $balance ? (float) $balance->allocated_days : (float) $leaveType->annual_allocation;
                $used = $balance ? (float) $balance->used_days : 0;
                $remaining = $balance ? (float) $balance->remaining_days : (float) $leaveType->annual_allocation;

                $data[] = [
                    'staff_name' => $this->localizedName($user->name, $user->name_mm),
                    'staff_id' => $user->staff_id ?? '—',
                    'department' => $user->department?->name ? $this->localizedName($user->department->name, $user->department->name_mm) : __('common.n_a'),
                    'leave_type' => $this->localizedName($leaveType->name, $leaveType->name_mm),
                    'allocated_days' => my_number($allocated),
                    'allocated_days_raw' => $allocated,
                    'used_days' => my_number($used),
                    'used_days_raw' => $used,
                    'remaining_days' => my_number($remaining),
                    'remaining_days_raw' => $remaining,
                    'is_not_limited' => $leaveType->is_not_limited,
                    'profile_image' => $user->profile_image,
                ];
            }
        }

        return response()->json($data);
    }

    private function buildLeaveSummaryChart(array $data): array
    {
        $grouped = [];
        foreach ($data as $item) {
            $type = $item['leave_type'];
            $grouped[$type] = ($grouped[$type] ?? 0) + (float) $item['total_days'];
        }

        $values = array_values($grouped);
        $maxValue = empty($values) ? 1 : (max($values) ?: 1);

        return [
            'labels' => array_keys($grouped),
            'values' => $values,
            'max' => $maxValue,
            'colors' => $this->chartPalette(count($grouped)),
        ];
    }

    private function buildBalanceChart(array $data): array
    {
        $used = (float) array_sum(array_column($data, 'used_days'));
        $remaining = (float) array_sum(array_column($data, 'remaining_days'));
        $maxValue = max($used, $remaining) ?: 1;

        return [
            'labels' => [__('common.used_days'), __('common.remaining_days')],
            'values' => [$used, $remaining],
            'max' => $maxValue,
            'colors' => ['#ef4444', '#22c55e'],
        ];
    }

    private function buildLeaveTypeChart(array $data): array
    {
        $grouped = [];
        foreach ($data as $item) {
            $type = $item['leave_type'];
            $grouped[$type] = ($grouped[$type] ?? 0) + (float) $item['total_days'];
        }

        $values = array_values($grouped);
        $maxValue = empty($values) ? 1 : (max($values) ?: 1);

        return [
            'labels' => array_keys($grouped),
            'values' => $values,
            'max' => $maxValue,
            'colors' => $this->chartPalette(count($grouped)),
        ];
    }

    private function buildBalancePerStaffChart(array $data): array
    {
        $grouped = [];
        foreach ($data as $item) {
            $name = $item['staff_name'] ?? '—';
            if (! isset($grouped[$name])) {
                $grouped[$name] = ['used' => 0.0, 'remaining' => 0.0];
            }

            if ($item['is_not_limited'] ?? false) {
                continue;
            }

            $grouped[$name]['used'] += (float) ($item['used_days'] ?? 0);
            $grouped[$name]['remaining'] += (float) ($item['remaining_days'] ?? 0);
        }

        $labels = array_values(array_keys($grouped));
        $used = array_values(array_map(fn ($v) => $v['used'], $grouped));
        $remaining = array_values(array_map(fn ($v) => $v['remaining'], $grouped));
        $maxValue = max([1, max(array_merge($used, $remaining) ?: [0])]);

        return [
            'labels' => $labels,
            'used' => $used,
            'remaining' => $remaining,
            'max' => $maxValue,
            'colors' => ['#ef4444', '#22c55e'],
        ];
    }

    private function buildDepartmentChart(array $data): array
    {
        $grouped = [];
        foreach ($data as $item) {
            $dept = $item['department'];
            $grouped[$dept] = ($grouped[$dept] ?? 0) + (float) $item['total_days'];
        }

        $values = array_values($grouped);
        $maxValue = empty($values) ? 1 : (max($values) ?: 1);

        return [
            'labels' => array_keys($grouped),
            'values' => $values,
            'max' => $maxValue,
            'colors' => $this->chartPalette(count($grouped)),
        ];
    }

    /**
     * @return list<string>
     */
    private function chartPalette(int $count): array
    {
        $palette = [
            '#3b82f6',
            '#0f766e',
            '#8b5cf6',
            '#f59e0b',
            '#ef4444',
            '#06b6d4',
            '#84cc16',
            '#ec4899',
        ];

        if ($count <= 0) {
            return [];
        }

        $colors = [];
        for ($i = 0; $i < $count; $i++) {
            $colors[] = $palette[$i % count($palette)];
        }

        return $colors;
    }

    private function mpdfTempDir(): string
    {
        $dir = storage_path('framework/cache/mpdf');

        if (! is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        return $dir;
    }

    private function clearMpdfFontCache(): void
    {
        $dir = storage_path('framework/cache/mpdf');

        if (! is_dir($dir)) {
            return;
        }

        $files = glob($dir.'/*mpdf*');
        foreach ($files as $file) {
            @unlink($file);
        }
    }

    private function buildFilterSummary(array $filters): array
    {
        $summary = [];

        if (! empty($filters['department_id'])) {
            $department = Department::find($filters['department_id']);
            $summary[__('admin.all_departments')] = $department ? $this->localizedName($department->name, $department->name_mm) : __('common.n_a');
        }

        if (! empty($filters['start_date'])) {
            $summary[__('common.start_date')] = $filters['start_date'];
        }

        if (! empty($filters['end_date'])) {
            $summary[__('common.end_date')] = $filters['end_date'];
        }

        if (! empty($filters['year'])) {
            $summary[__('common.year')] = $filters['year'];
        }

        if (! empty($filters['leave_type_id'])) {
            $leaveType = LeaveType::find($filters['leave_type_id']);
            $summary[__('common.leave_type')] = $leaveType ? $this->localizedName($leaveType->name, $leaveType->name_mm) : __('common.n_a');
        }

        return $summary;
    }
}
