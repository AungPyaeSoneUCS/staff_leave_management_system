<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $sortable = ['name', 'role', 'department', 'position', 'staff_id', 'is_active'];

        $sort = $request->query('sort', 'name');
        $direction = $request->query('direction', 'asc');

        if (! in_array($sort, $sortable, true)) {
            $sort = 'name';
        }

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $query = User::with('department')->where('role', '!=', 'super_admin');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('staff_id', 'like', "%{$search}%");
            });
        }

        if ($role = $request->query('role')) {
            $query->where('role', $role);
        }

        if ($departmentId = $request->query('department_id')) {
            $query->where('department_id', $departmentId);
        }

        if ($position = $request->query('position')) {
            $query->where(function ($q) use ($position) {
                $q->where('position', $position)
                    ->orWhere('position_mm', $position);
            });
        }

        if ($sort === 'department') {
            $query = $query->select('users.*')
                ->leftJoin('departments', 'users.department_id', '=', 'departments.id')
                ->orderBy('departments.name', $direction);
        } elseif ($sort === 'position') {
            $query = $query->orderByRaw($direction === 'asc' ? 'position IS NOT NULL DESC' : 'position IS NOT NULL ASC')
                ->orderBy('position', $direction);
        } elseif ($sort === 'is_active') {
            $query = $query->orderBy('is_active', $direction);
        } else {
            $query = $query->orderBy($sort, $direction);
        }

        $query = $query->orderBy('users.name');

        $users = $query->paginate(15)->withQueryString();

        $departments = Department::get();
        $positions = Config::get('positions', []);

        $nameSuggestions = User::where('role', '!=', 'super_admin')
            ->get(['name', 'name_mm'])
            ->flatMap(fn ($user) => array_values(array_filter([$user->name, $user->name_mm])))
            ->unique()
            ->values();

        return view('admin.users.index', compact('users', 'sort', 'direction', 'departments', 'positions', 'nameSuggestions'));
    }

    public function create()
    {
        $departments = Department::get();
        $positions = Config::get('positions', []);

        return view('admin.users.create', compact('departments', 'positions'));
    }

    public function store(Request $request)
    {
        $positions = Config::get('positions', []);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_mm' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).+$/',
            'role' => 'required|in:admin,department_head,staff',
            'department_id' => 'nullable|exists:departments,id',
            'staff_id' => 'nullable|string|unique:users',
            'phone' => 'nullable|string|min:9|max:11|regex:/^09[2-9][0-9]{6,8}$/',
            'position' => 'nullable|string|in:'.implode(',', array_keys($positions)),
            'position_mm' => 'nullable|string|in:'.implode(',', array_values($positions)),
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validated['role'] === 'admin' && ! auth()->user()->isSuperAdmin()) {
            abort(403, __('flash.only_super_admin_create'));
        }

        $validated['password'] = Hash::make($validated['password']);

        if ($request->hasFile('profile_image')) {
            $validated['profile_image'] = $request->file('profile_image')->store('profile-images', 'public');
        }

        User::create($validated);

        return redirect()->route('admin.users.index')
            ->with('success', __('flash.user_created'));
    }

    public function show(User $user)
    {
        return redirect()->route('admin.staff.show', $user);
    }

    public function edit(User $user)
    {
        $departments = Department::get();
        $positions = Config::get('positions', []);

        return view('admin.users.edit', compact('user', 'departments', 'positions'));
    }

    public function update(Request $request, User $user)
    {
        $positions = Config::get('positions', []);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_mm' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'password' => 'nullable|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).+$/',
            'role' => 'required|in:admin,department_head,staff',
            'department_id' => 'nullable|exists:departments,id',
            'staff_id' => 'nullable|string|unique:users,staff_id,'.$user->id,
            'phone' => 'nullable|string|min:9|max:11|regex:/^09[2-9][0-9]{6,8}$/',
            'position' => 'nullable|string|in:'.implode(',', array_keys($positions)),
            'position_mm' => 'nullable|string|in:'.implode(',', array_values($positions)),
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validated['role'] === 'admin' && ! auth()->user()->isSuperAdmin()) {
            abort(403, __('flash.only_super_admin_assign'));
        }

        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        if ($request->hasFile('profile_image')) {
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }
            $validated['profile_image'] = $request->file('profile_image')->store('profile-images', 'public');
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', __('flash.user_updated'));
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', __('flash.cannot_delete_self'));
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', __('flash.user_deleted'));
    }

    public function toggleActive(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', __('flash.cannot_delete_self'));
        }

        $user->update(['is_active' => ! $user->is_active]);

        if (! $user->is_active) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }

        return back()->with('success', $user->is_active ? __('flash.user_activated') : __('flash.user_deactivated'));
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

        $header = array_shift($rows);

        $positions = Config::get('positions', []);
        $departments = Department::pluck('name', 'id')->toArray();

        $previewData = [];
        $hasConflicts = false;

        foreach ($rows as $index => $row) {
            $row = array_pad($row, count($header), '');
            $row = array_slice($row, 0, count($header));
            $data = array_combine($header, $row);

            if (! $data) {
                continue;
            }

            $email = strtolower(trim($data['email'] ?? ''));
            $staffId = ! empty($data['staff_id']) ? trim($data['staff_id']) : null;
            $name = trim($data['name'] ?? '');

            if (empty($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL) || empty($name)) {
                continue;
            }

            $existingUser = User::where('email', $email)
                ->orWhere('staff_id', $staffId)
                ->first();

            $conflictType = null;
            if ($existingUser) {
                $hasConflicts = true;
                if ($existingUser->email === $email) {
                    $conflictType = 'email';
                }
                if ($existingUser->staff_id === $staffId && $staffId !== null) {
                    $conflictType = $conflictType ? 'both' : 'staff_id';
                }
            }

            $password = trim($data['password'] ?? '');
            if (empty($password) || strlen($password) < 8) {
                $password = 'password123';
            }

            $role = strtolower(trim($data['role'] ?? 'staff'));
            if (! in_array($role, ['super_admin', 'admin', 'department_head', 'staff'])) {
                $role = 'staff';
            }

            if ($role === 'admin' && ! auth()->user()->isSuperAdmin()) {
                $role = 'staff';
            }

            $departmentName = strtolower(trim($data['department'] ?? ''));
            $departmentId = null;
            foreach ($departments as $id => $deptName) {
                if (strtolower($deptName) === $departmentName) {
                    $departmentId = $id;
                    break;
                }
            }

            $position = trim($data['position'] ?? '');
            $positionMm = trim($data['position_mm'] ?? '');
            if (empty($position) && ! empty($positionMm)) {
                $position = array_search($positionMm, $positions);
            } elseif (! empty($position) && empty($positionMm)) {
                $positionMm = $positions[$position] ?? '';
            }

            $previewData[] = [
                'name' => $name,
                'name_mm' => ! empty($data['name_mm']) ? trim($data['name_mm']) : null,
                'email' => $email,
                'password' => $password,
                'role' => $role,
                'department_id' => $departmentId,
                'staff_id' => $staffId,
                'phone' => ! empty($data['phone']) ? trim($data['phone']) : null,
                'position' => $position ?: null,
                'position_mm' => $positionMm ?: null,
                'require_admin_approval' => strtolower(trim($data['require_admin_approval'] ?? '')) === 'yes' || strtolower(trim($data['require_admin_approval'] ?? '')) === '1',
                'conflict_type' => $conflictType,
                'existing_user' => $existingUser ? [
                    'name' => $existingUser->name,
                    'email' => $existingUser->email,
                    'staff_id' => $existingUser->staff_id,
                ] : null,
            ];
        }

        $request->session()->put('import_preview_data', $previewData);

        return view('admin.users.import-preview', compact('previewData', 'hasConflicts', 'departments'));
    }

    public function importProcess(Request $request)
    {
        $request->validate([
            'rows' => 'required|array',
            'rows.*' => 'required|string',
            'actions' => 'required|array',
            'actions.*' => 'required|in:skip,replace,import',
        ]);

        $previewData = $request->session()->get('import_preview_data', []);
        $request->session()->forget('import_preview_data');

        $selectedRows = $request->input('rows', []);
        $actions = $request->input('actions', []);

        $imported = 0;
        $skipped = 0;
        $replaced = 0;
        $errors = [];

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

            if ($action === 'import') {
                $userData = [
                    'name' => $data['name'],
                    'name_mm' => $data['name_mm'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                    'role' => $data['role'],
                    'department_id' => $data['department_id'],
                    'staff_id' => $data['staff_id'],
                    'phone' => $data['phone'],
                    'position' => $data['position'],
                    'position_mm' => $data['position_mm'],
                    'is_active' => true,
                    'require_admin_approval' => $data['require_admin_approval'],
                ];

                User::create($userData);
                $imported++;

                continue;
            }

            if ($action === 'replace') {
                $existingUser = User::where('email', $data['email'])
                    ->orWhere('staff_id', $data['staff_id'])
                    ->first();

                if ($existingUser) {
                    $updateData = [
                        'name' => $data['name'],
                        'name_mm' => $data['name_mm'],
                        'password' => Hash::make($data['password']),
                        'role' => $data['role'],
                        'department_id' => $data['department_id'],
                        'staff_id' => $data['staff_id'],
                        'phone' => $data['phone'],
                        'position' => $data['position'],
                        'position_mm' => $data['position_mm'],
                        'is_active' => true,
                        'require_admin_approval' => $data['require_admin_approval'],
                    ];

                    $existingUser->update($updateData);
                    $replaced++;
                } else {
                    User::create($data);
                    $imported++;
                }
            }
        }

        $message = __('flash.import_completed', [
            'imported' => $imported,
            'replaced' => $replaced,
            'skipped' => $skipped,
        ]);

        if (! empty($errors)) {
            $message .= ' Errors: '.implode('; ', array_slice($errors, 0, 10));
        }

        return redirect()->route('admin.users.index')->with('success', $message);
    }

    public function importTemplate()
    {
        $headers = [
            'name',
            'name_mm',
            'email',
            'password',
            'role',
            'department',
            'staff_id',
            'phone',
            'position',
            'position_mm',
            'require_admin_approval',
        ];

        $callback = function () use ($headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            fclose($file);
        };

        return response()->streamDownload($callback, 'users-import-template.csv', [
            'Content-Type' => 'text/csv',
        ]);
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

        $sheetXml = simplexml_load_string($zip->getFromName('xl/worksheets/sheet1.xml'));
        $rows = [];

        if ($sheetXml && isset($sheetXml->sheetData->row)) {
            foreach ($sheetXml->sheetData->row as $row) {
                $rowData = [];
                foreach ($row->c as $cell) {
                    $cellRef = (string) $cell['r'];
                    $col = preg_replace('/[0-9]/', '', $cellRef);
                    $colIndex = $this->columnToIndex($col);
                    $value = $this->getCellValue($cell, $sharedStrings);
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

    private function getCellValue(\SimpleXMLElement $cell, array $sharedStrings): string
    {
        $value = (string) $cell->v;

        if ((string) $cell['t'] === 's' && isset($sharedStrings[(int) $value])) {
            return $sharedStrings[(int) $value];
        }

        return $value;
    }
}
