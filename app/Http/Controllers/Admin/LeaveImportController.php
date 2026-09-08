<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use App\Services\LeaveBalanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LeaveImportController extends Controller
{
    public function index()
    {
        return view('admin.leave-import.index');
    }

    public function importPreview(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,csv|max:2048',
        ]);

        $file = $request->file('import_file');
        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension === 'xlsx') {
            $rows = $this->parseXlsx($file->getPathname());
        } else {
            $rows = $this->parseCsv($file->getPathname());
        }

        if (empty($rows)) {
            return back()->with('error', __('flash.no_data_found'));
        }

        $header = array_map(function ($h) {
            return strtolower(trim((string) $h));
        }, array_shift($rows));

        $staffByKey = $this->staffLookup();
        $leaveTypesByCode = LeaveType::all()->keyBy(fn ($lt) => strtolower(trim($lt->code)));
        $leaveTypesByName = LeaveType::all()->keyBy(fn ($lt) => strtolower(trim($lt->name)));
        $leaveTypesByNameMm = LeaveType::all()->keyBy(fn ($lt) => strtolower(trim($lt->name_mm ?? '')));

        $statuses = ['pending', 'approved', 'rejected', 'revoked', 'cancelled'];

        $previewData = [];
        $skippedCount = 0;

        foreach ($rows as $index => $row) {
            $row = array_pad($row, count($header), '');
            $row = array_slice($row, 0, count($header));
            $data = [];
            foreach ($header as $i => $key) {
                $data[$key] = $row[$i] ?? '';
            }

            $staffKey = strtolower(trim($data['staff_id'] ?? ''));
            $user = null;
            if ($staffKey !== '' && isset($staffByKey['staff_id'][$staffKey])) {
                $user = $staffByKey['staff_id'][$staffKey];
            }

            $leaveTypeCode = strtolower(trim($data['leave_type'] ?? ''));
            $leaveType = $leaveTypesByCode->get($leaveTypeCode);
            if (! $leaveType && $leaveTypeCode !== '') {
                $leaveType = $leaveTypesByName->get($leaveTypeCode) ?? $leaveTypesByNameMm->get($leaveTypeCode);
            }

            $startDate = $this->normalizeDate($data['start_date'] ?? '');
            $endDate = $this->normalizeDate($data['end_date'] ?? '');

            if (! $user || ! $leaveType || ! $startDate) {
                $skippedCount++;

                continue;
            }

            if (! $endDate) {
                $endDate = $startDate;
            }

            $isHalfDay = stripos(trim($data['is_half_day'] ?? ''), 'yes') !== false
                || trim($data['is_half_day'] ?? '') === '1'
                || stripos(trim($data['is_half_day'] ?? ''), 'true') !== false;

            $totalDays = trim($data['total_days'] ?? '') !== ''
                ? (float) $data['total_days']
                : $this->calculateTotalDays($startDate, $endDate, $isHalfDay);

            $dutyExchangeUserId = null;
            $dutyExchangeName = null;
            $dutyExchangeStaffId = null;
            $dutyExchangeKey = strtolower(trim($data['duty_exchange'] ?? ''));
            if ($dutyExchangeKey !== '') {
                $dutyExchangeUser = null;
                if (isset($staffByKey['staff_id'][$dutyExchangeKey])) {
                    $dutyExchangeUser = $staffByKey['staff_id'][$dutyExchangeKey];
                } elseif (isset($staffByKey['email'][$dutyExchangeKey])) {
                    $dutyExchangeUser = $staffByKey['email'][$dutyExchangeKey];
                }
                if ($dutyExchangeUser) {
                    $dutyExchangeUserId = $dutyExchangeUser->id;
                    $dutyExchangeName = $dutyExchangeUser->name;
                    $dutyExchangeStaffId = $dutyExchangeUser->staff_id;
                }
            }

            $status = strtolower(trim($data['status'] ?? ''));
            if (! in_array($status, $statuses)) {
                $status = 'approved';
            }

            $previewData[] = [
                'user_id' => $user->id,
                'staff_id' => $user->staff_id,
                'staff_name' => $user->name,
                'leave_type_id' => $leaveType->id,
                'leave_type_name' => $leaveType->name,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'total_days' => $totalDays,
                'is_half_day' => $isHalfDay ? (int) $isHalfDay : 0,
                'duty_exchange_user_id' => $dutyExchangeUserId,
                'duty_exchange_name' => $dutyExchangeName,
                'duty_exchange_staff_id' => $dutyExchangeStaffId,
                'status' => $status,
            ];
        }

        if (empty($previewData)) {
            return back()->with('error', __('admin.leave_import_no_valid_rows', ['skipped' => $skippedCount]));
        }

        $request->session()->put('leave_import_preview_data', $previewData);

        $hasConflicts = false;

        return view('admin.leave-import.import-preview', compact('previewData', 'hasConflicts', 'skippedCount'));
    }

    public function importProcess(Request $request)
    {
        $request->validate([
            'rows' => 'required|array',
            'rows.*' => 'required|string',
            'actions' => 'required|array',
            'actions.*' => 'required|in:skip,import',
        ]);

        $previewData = $request->session()->get('leave_import_preview_data', []);
        $request->session()->forget('leave_import_preview_data');

        $selectedRows = $request->input('rows', []);
        $actions = $request->input('actions', []);

        $imported = 0;
        $skipped = 0;
        $balanceService = app(LeaveBalanceService::class);

        foreach ($selectedRows as $rowIndex) {
            if (! isset($previewData[$rowIndex])) {
                continue;
            }

            $data = $previewData[$rowIndex];
            $action = $actions[$rowIndex] ?? 'skip';

            if ($action === 'skip') {
                $skipped++;

                continue;
            }

            $user = User::find($data['user_id']);
            $leaveType = LeaveType::find($data['leave_type_id']);

            if (! $user || ! $leaveType) {
                $skipped++;

                continue;
            }

            $leaveRequest = LeaveRequest::create([
                'user_id' => $user->id,
                'leave_type_id' => $leaveType->id,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'total_days' => $data['total_days'],
                'reason' => 'Imported',
                'status' => $data['status'],
                'current_approval_level' => $data['status'] === 'approved' ? 2 : 0,
                'is_half_day' => (bool) $data['is_half_day'],
                'duty_exchange_user_id' => $data['duty_exchange_user_id'] ?? null,
            ]);

            if ($data['status'] === 'approved') {
                $balanceService->updateUsedDays($user, $leaveType, (float) $data['total_days']);
            }

            $imported++;
        }

        $message = __('flash.leave_import_completed', [
            'imported' => $imported,
            'skipped' => $skipped,
        ]);

        return redirect()->route('admin.leave-import.index')->with('success', $message);
    }

    public function importTemplate()
    {
        $headers = [
            'staff_id',
            'leave_type',
            'start_date',
            'end_date',
            'total_days',
            'is_half_day',
            'status',
            'duty_exchange',
        ];

        $sample = [
            ['STF-001', 'AL', '2026-01-01', '2026-01-03', '', 'no', 'approved', ''],
            ['STF-002', 'SL', '2026-02-10', '2026-02-10', '0.5', 'yes', 'approved', 'STF-001'],
        ];

        $callback = function () use ($headers, $sample) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            foreach ($sample as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->streamDownload($callback, 'leave-history-import-template.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function staffLookup(): array
    {
        $staff = User::whereIn('role', ['staff', 'department_head', 'admin'])->get();

        $byStaffId = [];
        $byEmail = [];
        foreach ($staff as $user) {
            if (! empty($user->staff_id)) {
                $byStaffId[strtolower(trim($user->staff_id))] = $user;
            }
            if (! empty($user->email)) {
                $byEmail[strtolower(trim($user->email))] = $user;
            }
        }

        return [
            'staff_id' => $byStaffId,
            'email' => $byEmail,
        ];
    }

    private function normalizeDate(?string $date): ?string
    {
        $date = trim((string) $date);
        if ($date === '') {
            return null;
        }

        foreach (['Y-m-d', 'd/m/Y', 'm/d/Y', 'd-m-Y'] as $format) {
            try {
                $parsed = Carbon::createFromFormat($format, $date);
                if ($parsed && $parsed->format($format) === $date) {
                    return $parsed->format('Y-m-d');
                }
            } catch (\Throwable $e) {
                // try next format
            }
        }

        // Excel serial date number fallback (e.g. 46002 => 2026-01-01)
        if (is_numeric($date) && (float) $date > 20000 && (float) $date < 80000) {
            return $this->excelSerialToDate((float) $date);
        }

        try {
            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function calculateTotalDays(string $startDate, string $endDate, bool $halfDay): float
    {
        $workflow = app(\App\Services\LeaveWorkflowService::class);

        return (float) $workflow->calculateTotalDays($startDate, $endDate, $halfDay);
    }

    private function parseXlsx(string $filePath): array
    {
        $zip = new \ZipArchive;
        $zip->open($filePath);

        $sharedStrings = [];
        if ($zip->locateName('xl/sharedStrings.xml') !== false) {
            $xml = simplexml_load_string($zip->getFromName('xl/sharedStrings.xml'));
            foreach ($xml->si as $si) {
                $text = '';
                foreach ($si->t as $node) {
                    $text .= (string) $node;
                }
                $sharedStrings[] = $text;
            }
        }

        // Determine which cell style indexes represent date/number formats.
        $dateFormatIds = [14, 15, 16, 17, 18, 19, 20, 21, 22, 45, 46, 47];
        $dateStyleIndexes = [];
        if ($zip->locateName('xl/styles.xml') !== false) {
            $stylesXml = simplexml_load_string($zip->getFromName('xl/styles.xml'));

            $customDateIds = [];
            if (isset($stylesXml->numFmts)) {
                foreach ($stylesXml->numFmts->numFmt as $fmt) {
                    $id = (string) $fmt['numFmtId'];
                    $code = (string) $fmt['formatCode'];
                    if ($this->isDateFormatCode($code) && ! in_array($id, $dateFormatIds)) {
                        $customDateIds[] = $id;
                    }
                }
            }
            $dateFormatIds = array_merge($dateFormatIds, $customDateIds);

            if (isset($stylesXml->cellXfs)) {
                foreach ($stylesXml->cellXfs->xf as $i => $xf) {
                    if (in_array((string) $xf['numFmtId'], $dateFormatIds)) {
                        $dateStyleIndexes[] = (string) $i;
                    }
                }
            }
        }

        $sheetXml = simplexml_load_string($zip->getFromName('xl/worksheets/sheet1.xml'));
        $rows = [];

        if ($sheetXml && isset($sheetXml->sheetData->row)) {
            foreach ($sheetXml->sheetData->row as $row) {
                $rowData = [];
                foreach ($row->c as $cell) {
                    $cellRef = (string) $cell['r'];
                    $col = preg_replace('/[0-9]/', '', $cellRef);
                    $colIndex = $this->columnToIndex($col);
                    $value = $this->getCellValue($cell, $sharedStrings, $dateStyleIndexes);
                    $rowData[$colIndex] = $value;
                }
                ksort($rowData);
                $rows[] = array_values($rowData);
            }
        }

        $zip->close();

        return $rows;
    }

    private function parseCsv(string $filePath): array
    {
        $rows = [];
        if (($handle = fopen($filePath, 'r')) !== false) {
            while (($row = fgetcsv($handle)) !== false) {
                $rows[] = $row;
            }
            fclose($handle);
        }

        return $rows;
    }

    private function columnToIndex(string $column): int
    {
        $column = strtoupper($column);
        $index = 0;
        $length = strlen($column);
        for ($i = 0; $i < $length; $i++) {
            $index = $index * 26 + (ord($column[$i]) - 64);
        }

        return $index - 1;
    }

    private function getCellValue(\SimpleXMLElement $cell, array $sharedStrings, array $dateStyleIndexes = []): string
    {
        $value = (string) $cell->v;

        if ((string) $cell['t'] === 's' && isset($sharedStrings[(int) $value])) {
            return $sharedStrings[(int) $value];
        }

        // A numeric cell styled as a date holds an Excel serial number -> convert it.
        $style = (string) $cell['s'];
        if ($value !== '' && is_numeric($value) && $style !== '' && in_array($style, $dateStyleIndexes)) {
            return $this->excelSerialToDate((float) $value);
        }

        return $value;
    }

    private function isDateFormatCode(string $code): bool
    {
        return (bool) preg_match('/([dmyhs]|\[hh\])/i', $code);
    }

    private function excelSerialToDate(float $serial): string
    {
        // Excel 1900 date system: serial 1 = 1900-01-01 (epoch 1899-12-30 accounts for the fake leap day).
        $days = floor($serial);
        $seconds = (int) round(($serial - $days) * 86400);

        $epoch = \Illuminate\Support\Carbon::create(1899, 12, 30, 0, 0, 0);

        return $epoch->copy()->addDays($days)->addSeconds($seconds)->format('Y-m-d');
    }
}
