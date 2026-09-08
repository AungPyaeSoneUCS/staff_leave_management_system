<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\HolidayController;
use App\Http\Controllers\Admin\LeaveImportController;
use App\Http\Controllers\Admin\LeaveTypeController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\StaffController as AdminStaffController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\CentralAdmin\ApprovalController as CentralAdminApprovalController;
use App\Http\Controllers\DepartmentHead\ApprovalController as DepartmentHeadApprovalController;
use App\Http\Controllers\DepartmentHead\DashboardController as DepartmentHeadDashboardController;
use App\Http\Controllers\DepartmentHead\ProfileController as DepartmentProfileController;
use App\Http\Controllers\DepartmentHead\StaffController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Staff\CalendarController;
use App\Http\Controllers\Staff\DashboardController as StaffDashboardController;
use App\Http\Controllers\Staff\LeaveRequestController;
use App\Http\Controllers\Staff\LeaveTypeController as StaffLeaveTypeController;
use App\Http\Controllers\Staff\ProfileController;
use App\Http\Controllers\SuperAdmin\AdminAssignmentController;
use App\Http\Controllers\SuperAdmin\AdminManagementController;
use App\Http\Controllers\SuperAdmin\RegisterController as SuperAdminRegisterController;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        if ($user->role === 'super_admin' || $user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($user->role === 'department_head') {
            return redirect()->route('department-head.dashboard');
        } elseif ($user->role === 'staff') {
            return redirect()->route('staff.dashboard');
        } else {
            return redirect()->route('login');
        }
    }

    $leaveTypes = LeaveType::where('is_active', true)->get();

    return view('auth.landing', compact('leaveTypes'));
});

Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    Route::post('/login', function (Request $request) {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'role' => ['required', 'in:super_admin,admin,department_head,staff'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => __('auth.provided_credentials'),
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if ($user->role !== $credentials['role']) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => __('auth.role_mismatch'),
            ])->onlyInput('email');
        }

        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => __('auth.account_deactivated'),
            ])->onlyInput('email');
        }

        $user = Auth::user();
        if ($user->role === 'super_admin' || $user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($user->role === 'department_head') {
            return redirect()->route('department-head.dashboard');
        } elseif ($user->role === 'staff') {
            return redirect()->route('staff.dashboard');
        } else {
            return redirect('/');
        }
    })->name('login.store');

    Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotForm'])->name('password.forgot');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendOtp'])->name('password.send.otp');
    Route::get('/verify-otp', [ForgotPasswordController::class, 'showVerifyOtpForm'])->name('password.verify.otp');
    Route::post('/verify-otp', [ForgotPasswordController::class, 'verifyOtp'])->name('password.verify');
    Route::get('/reset-password', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset.form');
    Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('password.reset');

    Route::get('/super-admin/register', [SuperAdminRegisterController::class, 'create'])
        ->name('super-admin.register');
    Route::post('/super-admin/register/key', [SuperAdminRegisterController::class, 'verifyKey'])
        ->name('super-admin.register.key');
    Route::post('/super-admin/register', [SuperAdminRegisterController::class, 'store']);
});

Route::get('lang/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'my'])) {
        session()->put('locale', $locale);
    }

    return back();
})->name('lang.switch');

Route::middleware('auth')->group(function () {
    Route::get('/notifications', function () {
        return redirect()->route('notifications.all');
    });
    Route::get('/notifications/data', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/all', [NotificationController::class, 'all'])->name('notifications.all');
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::put('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    Route::post('/logout', function (Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    })->name('logout');

    Route::prefix('staff')->name('staff.')->middleware('role:staff')->group(function () {
        Route::get('/dashboard', [StaffDashboardController::class, 'index'])->name('dashboard');
        Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar');
        Route::resource('leave-requests', LeaveRequestController::class);
        Route::post('/leave-requests/{leave_request}/cancel', [LeaveRequestController::class, 'cancel'])->name('leave-requests.cancel');
        Route::get('/leave-types', [StaffLeaveTypeController::class, 'index'])->name('leave-types.index');
        Route::get('/leave-types/{leaveType}', [StaffLeaveTypeController::class, 'show'])->name('leave-types.show');
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    });

    Route::prefix('department-head')->name('department-head.')->middleware('role:department_head')->group(function () {
        Route::get('/dashboard', [DepartmentHeadDashboardController::class, 'index'])->name('dashboard');
        Route::get('/reports/leave', [DepartmentHeadDashboardController::class, 'leaveReport'])->name('reports.leave');
        Route::get('/reports/leave-data', [DepartmentHeadDashboardController::class, 'getLeaveReportData'])->name('reports.leave-data');
        Route::post('/reports/export', [DepartmentHeadDashboardController::class, 'exportPdf'])->name('reports.export');
        Route::post('/reports/export-xlsx', [DepartmentHeadDashboardController::class, 'exportXlsx'])->name('reports.export-xlsx');
        Route::get('/approvals/pending', [DepartmentHeadApprovalController::class, 'pendingRequests'])->name('approvals.pending');
        Route::get('/approvals/history', [DepartmentHeadApprovalController::class, 'history'])->name('approvals.history');
        Route::get('/approvals/{leave_request}', [DepartmentHeadApprovalController::class, 'show'])->name('approvals.show');
        Route::post('/approvals/{leave_request}/approve', [DepartmentHeadApprovalController::class, 'approve'])->name('approvals.approve');
        Route::post('/approvals/{leave_request}/reject', [DepartmentHeadApprovalController::class, 'reject'])->name('approvals.reject');
        Route::post('/approvals/{leave_request}/revoke', [DepartmentHeadApprovalController::class, 'revoke'])->name('approvals.revoke');
        Route::resource('leave-requests', LeaveRequestController::class);
        Route::post('/leave-requests/{leave_request}/cancel', [LeaveRequestController::class, 'cancel'])->name('leave-requests.cancel');
        Route::get('/profile', [DepartmentProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [DepartmentProfileController::class, 'update'])->name('profile.update');
        Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
        Route::get('/staff/{user}', [StaffController::class, 'show'])->name('staff.show');
    });

    Route::prefix('central-admin')->name('central-admin.')->middleware('role:admin,super_admin')->group(function () {
        Route::get('/approvals/pending', [CentralAdminApprovalController::class, 'pendingRequests'])->name('approvals.pending');
        Route::get('/approvals/history', [CentralAdminApprovalController::class, 'history'])->name('approvals.history');
        Route::get('/approvals/{leave_request}', [CentralAdminApprovalController::class, 'show'])->name('approvals.show');
        Route::post('/approvals/bulk', [CentralAdminApprovalController::class, 'bulk'])->name('approvals.bulk');
        Route::post('/approvals/{leave_request}/approve', [CentralAdminApprovalController::class, 'approve'])->name('approvals.approve');
        Route::post('/approvals/{leave_request}/reject', [CentralAdminApprovalController::class, 'reject'])->name('approvals.reject');
        Route::post('/approvals/{leave_request}/revoke', [CentralAdminApprovalController::class, 'revoke'])->name('approvals.revoke');
    });

    Route::prefix('admin')->name('admin.')->middleware('role:admin,super_admin')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('departments', DepartmentController::class);
        Route::get('/users/import', function () {
            return redirect()->route('admin.users.index');
        });
        Route::post('/users/import', [UserController::class, 'importPreview'])->name('users.import');
        Route::post('/users/import-process', [UserController::class, 'importProcess'])->name('users.import-process');
        Route::get('/users/import-template', [UserController::class, 'importTemplate'])->name('users.import-template');
        Route::resource('users', UserController::class)->except(['show']);
        Route::post('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
        Route::resource('leave-types', LeaveTypeController::class);
        Route::post('/leave-types/{leave_type}/reallocate', [LeaveTypeController::class, 'reallocateBalances'])->name('leave-types.reallocate');
        Route::get('/leave-import', [LeaveImportController::class, 'index'])->name('leave-import.index');
        Route::post('/leave-import', [LeaveImportController::class, 'importPreview'])->name('leave-import.preview');
        Route::post('/leave-import/process', [LeaveImportController::class, 'importProcess'])->name('leave-import.process');
        Route::get('/leave-import/template', [LeaveImportController::class, 'importTemplate'])->name('leave-import.template');
        Route::get('/holidays', [HolidayController::class, 'index'])->name('holidays.index');
        Route::get('/holidays/calendar', [HolidayController::class, 'calendar'])->name('holidays.calendar');
        Route::post('/holidays', [HolidayController::class, 'store'])->name('holidays.store');
        Route::get('/holidays/{holiday}/edit', [HolidayController::class, 'edit'])->name('holidays.edit');
        Route::put('/holidays/{holiday}', [HolidayController::class, 'update'])->name('holidays.update');
        Route::delete('/holidays/{holiday}', [HolidayController::class, 'destroy'])->name('holidays.destroy');
        Route::post('/holidays/ajax', [HolidayController::class, 'storeAjax'])->name('holidays.store-ajax');
        Route::put('/holidays/{holiday}/ajax', [HolidayController::class, 'updateAjax'])->name('holidays.update-ajax');
        Route::delete('/holidays/{holiday}/ajax', [HolidayController::class, 'destroyAjax'])->name('holidays.destroy-ajax');
        Route::get('/holidays/by-date', [HolidayController::class, 'byDate'])->name('holidays.by-date');
        Route::post('/holidays/clear-month', [HolidayController::class, 'clearMonth'])->name('holidays.clear-month');
        Route::get('/reports/leave-summary', [AdminDashboardController::class, 'leaveSummaryReport'])->name('reports.leave-summary');
        Route::get('/reports/balance', [AdminDashboardController::class, 'balanceReport'])->name('reports.balance');
        Route::get('/reports/leave-type', [AdminDashboardController::class, 'leaveTypeReport'])->name('reports.leave-type');
        Route::get('/reports/department', [AdminDashboardController::class, 'departmentReport'])->name('reports.department');
        Route::get('/reports/daily', [AdminDashboardController::class, 'dailyReport'])->name('reports.daily');
        Route::post('/reports/export', [AdminDashboardController::class, 'exportPdf'])->name('reports.export');
        Route::post('/reports/export-xlsx', [AdminDashboardController::class, 'exportXlsx'])->name('reports.export-xlsx');
        Route::get('/reports/leave-summary-data', [AdminDashboardController::class, 'getLeaveSummaryData'])->name('reports.leave-summary-data');
        Route::get('/reports/balance-data', [AdminDashboardController::class, 'getBalanceData'])->name('reports.balance-data');
        Route::get('/reports/leave-type-data', [AdminDashboardController::class, 'getLeaveTypeData'])->name('reports.leave-type-data');
        Route::get('/reports/department-data', [AdminDashboardController::class, 'getDepartmentData'])->name('reports.department-data');
        Route::get('/reports/daily-data', [AdminDashboardController::class, 'getDailyReportData'])->name('reports.daily-data');
        Route::get('/profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
        Route::get('/staff', [AdminStaffController::class, 'index'])->name('staff.index');
        Route::get('/staff/{user}', [AdminStaffController::class, 'show'])->name('staff.show');

    });
    Route::prefix('super-admin')->name('super-admin.')->middleware('role:super_admin')->group(function () {
        Route::get('/admins', [AdminManagementController::class, 'index'])->name('admins.index');
        Route::get('/admins/create', [AdminManagementController::class, 'create'])->name('admins.create');
        Route::post('/admins', [AdminManagementController::class, 'store'])->name('admins.store');
        Route::get('/admins/{user}/edit', [AdminManagementController::class, 'edit'])->name('admins.edit');
        Route::put('/admins/{user}', [AdminManagementController::class, 'update'])->name('admins.update');
        Route::delete('/admins/{user}', [AdminManagementController::class, 'destroy'])->name('admins.destroy');
        Route::post('/admins/{user}/toggle-active', [AdminManagementController::class, 'toggleActive'])->name('admins.toggle-active');
        Route::get('/assignments', [AdminAssignmentController::class, 'index'])->name('assignments.index');
        Route::post('/assignments', [AdminAssignmentController::class, 'store'])->name('assignments.store');
        Route::delete('/assignments/{admin_assignment}', [AdminAssignmentController::class, 'destroy'])->name('assignments.destroy');
        Route::get('/assignments/pending', [AdminAssignmentController::class, 'pending'])->name('assignments.pending');
    });
});
