<?php

namespace App\Http\Controllers\DepartmentHead;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use App\Services\LeaveBalanceService;
use App\Support\MyanmarDateFormatter;
use Illuminate\Http\Request;
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
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class DashboardController extends Controller
{
    public function __construct(
        private LeaveBalanceService $leaveBalanceService
    ) {}

    public function index()
    {
        $user = auth()->user();
        $departmentId = $user->department_id;

        $pendingApprovals = LeaveRequest::where('status', 'pending')
            ->where('current_approval_level', 1)
            ->whereHas('user', fn ($query) => $query->where('department_id', $departmentId)
                ->where('require_admin_approval', false))
            ->count();

        $departmentStaff = User::where('department_id', $departmentId)
            ->whereIn('role', ['staff', 'department_head'])
            ->count();

        $approvedThisMonth = LeaveRequest::where('status', 'approved')
            ->where('reviewer_id', $user->id)
            ->whereMonth('reviewed_at', now()->month)
            ->count();

        $leaveBalances = $this->leaveBalanceService->calculateAnnualLeaveBalance($user, now()->year);
        $recentRequests = $user->leaveRequests()->with('leaveType')->latest()->take(5)->get();

        return view('department-head.dashboard', compact(
            'pendingApprovals',
            'departmentStaff',
            'approvedThisMonth',
            'leaveBalances',
            'recentRequests'
        ));
    }

    public function leaveReport()
    {
        $departmentId = auth()->user()->department_id;
        $leaveTypes = LeaveType::where('is_active', true)->get();
        $currentYear = now()->year;

        return view('department-head.reports.leave', compact('departmentId', 'leaveTypes', 'currentYear'));
    }

    public function getLeaveReportData(Request $request)
    {
        $departmentId = auth()->user()->department_id;
        $filters = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        $query = $this->leaveReportQuery($departmentId, $filters);
        $data = $this->mapLeaveReportData($query->get());

        $allLeaveTypes = LeaveType::where('is_active', true)->get()->mapWithKeys(function ($lt) {
            $localizedName = app()->getLocale() == 'my' ? ($lt->name_mm ?? $lt->name) : $lt->name;

            return [$lt->name => $localizedName];
        });
        $actualData = $query->get()->groupBy('leaveType.name')->map(fn ($items) => $items->sum('total_days'));

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

    public function exportPdf(Request $request)
    {
        $departmentId = auth()->user()->department_id;
        $filters = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        $query = $this->leaveReportQuery($departmentId, $filters);
        $data = $this->mapLeaveReportData($query->get());

        $allLeaveTypes = LeaveType::where('is_active', true)->get()->mapWithKeys(function ($lt) {
            $localizedName = app()->getLocale() == 'my' ? ($lt->name_mm ?? $lt->name) : $lt->name;

            return [$lt->name => $localizedName];
        });
        $actualData = $query->get()->groupBy('leaveType.name')->map(fn ($items) => $items->sum('total_days'));

        $chartLabels = $allLeaveTypes->values()->toArray();
        $chartValues = $allLeaveTypes->keys()->map(fn ($englishName) => $actualData[$englishName] ?? 0)->values()->toArray();

        $html = view('department-head.reports.pdf', [
            'data' => $data,
            'filters' => $filters,
            'chart' => [
                'labels' => $chartLabels,
                'values' => $chartValues,
            ],
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

        $filename = 'department-leave-report-'.now()->format('Y-m-d').'.pdf';

        $content = $mpdf->Output('', Destination::STRING_RETURN);

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Length' => strlen($content),
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function exportXlsx(Request $request)
    {
        $departmentId = auth()->user()->department_id;
        $filters = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        $query = $this->leaveReportQuery($departmentId, $filters);
        $data = $this->mapLeaveReportData($query->get());

        $allLeaveTypes = LeaveType::where('is_active', true)->get()->mapWithKeys(function ($lt) {
            $localizedName = app()->getLocale() == 'my' ? ($lt->name_mm ?? $lt->name) : $lt->name;

            return [$lt->name => $localizedName];
        });
        $actualData = $query->get()->groupBy('leaveType.name')->map(fn ($items) => $items->sum('total_days'));

        $chart = [
            'labels' => $allLeaveTypes->values()->toArray(),
            'values' => $allLeaveTypes->keys()->map(fn ($englishName) => $actualData[$englishName] ?? 0)->values()->toArray(),
        ];

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Leave Report');

        $rowNum = 1;
        $sheet->mergeCells('A'.$rowNum.':H'.$rowNum);
        $sheet->setCellValue('A'.$rowNum, __('department_head.leave_report'));
        $sheet->getStyle('A'.$rowNum)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A'.$rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $rowNum++;

        $sheet->setCellValue('A'.$rowNum, __('admin.generated').': '.now()->format('Y-m-d H:i'));
        $rowNum++;

        if (! empty($filters['start_date']) || ! empty($filters['end_date'])) {
            $filterText = [];
            if (! empty($filters['start_date'])) {
                $filterText[] = __('common.start_date').': '.$filters['start_date'];
            }
            if (! empty($filters['end_date'])) {
                $filterText[] = __('common.end_date').': '.$filters['end_date'];
            }
            $sheet->setCellValue('A'.$rowNum, implode(' | ', $filterText));
            $rowNum++;
        }
        $rowNum++;

        $headers = [__('common.number'), __('common.name'), __('common.staff_id'), __('common.leave_type'), __('common.start_date'), __('common.end_date'), __('common.total_days'), __('common.status')];
        $sheet->fromArray($headers, null, 'A'.$rowNum);
        $sheet->getStyle('A'.$rowNum)->getFont()->setBold(true);
        $sheet->getStyle('A'.$rowNum)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0f766e');
        $sheet->getStyle('A'.$rowNum)->getFont()->getColor()->setRGB('FFFFFF');
        $rowNum++;

        foreach ($data as $index => $item) {
            $sheet->setCellValue('A'.$rowNum, config('app.locale') == 'my' ? my_number($index + 1) : $index + 1);
            $sheet->setCellValue('B'.$rowNum, $item['staff_name']);
            $sheet->setCellValue('C'.$rowNum, $item['staff_id']);
            $sheet->setCellValue('D'.$rowNum, $item['leave_type']);
            $sheet->setCellValue('E'.$rowNum, $item['start_date']);
            $sheet->setCellValue('F'.$rowNum, $item['end_date']);
            $sheet->setCellValue('G'.$rowNum, $item['is_not_limited'] ? '-' : $item['total_days']);
            $sheet->setCellValue('H'.$rowNum, $item['status']);
            $rowNum++;
        }

        if (! empty($chart['labels'])) {
            $this->addXlsxChart($spreadsheet, $sheet, $chart, $rowNum, count($headers));
        }

        $sheet->getDefaultColumnDimension()->setWidth(18);
        $sheet->getColumnDimension('B')->setWidth(30);

        $filename = 'department-leave-report-'.now()->format('Y-m-d').'.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->setIncludeCharts(true);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function leaveReportQuery(int $departmentId, array $filters)
    {
        return LeaveRequest::query()
            ->with(['user', 'leaveType', 'user.department'])
            ->whereHas('user', fn ($q) => $q->where('department_id', $departmentId))
            ->where('status', '!=', 'pending')
            ->when(! empty($filters['start_date']), fn ($query) => $query->whereDate('start_date', '>=', $filters['start_date']))
            ->when(! empty($filters['end_date']), fn ($query) => $query->whereDate('end_date', '<=', $filters['end_date']))
            ->orderByDesc('start_date');
    }

    private function mapLeaveReportData($query)
    {
        return $query->map(function ($item) {
            return [
                'staff_name' => app()->getLocale() == 'my' ? ($item->user->name_mm ?? $item->user->name) : $item->user->name,
                'staff_id' => $item->user->staff_id ?? '—',
                'leave_type' => app()->getLocale() == 'my' ? ($item->leaveType->name_mm ?? $item->leaveType->name) : $item->leaveType->name,
                'start_date' => MyanmarDateFormatter::format($item->start_date, 'F d, Y'),
                'end_date' => $item->end_date ? MyanmarDateFormatter::format($item->end_date, 'F d, Y') : '—',
                'total_days' => my_number($item->total_days),
                'is_not_limited' => $item->leaveType->is_not_limited,
                'status' => __('common.'.$item->status),
            ];
        })->values()->toArray();
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
}
