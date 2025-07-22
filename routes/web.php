<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\{
    LoginController,
    LogoutController,
    ForgotPasswordController,
    ResetPasswordController
};
use App\Http\Controllers\{
    DashboardController,
    EmployeeDashboardController,
    LeaveController,
    PayslipController,
    AttendanceController,
    ReportController,
    ApprovalController,
    UserController,
    DepartmentController,
    DesignationController,
    EmployeeController,
    PayrollController,
    ScheduleController,
    PerformanceFormController,
    EvaluationController,
    EmployeeEvaluationController,
    ProfileController,
    AuditLogController,
    AnnouncementController,
    LeaveTypeController,
    LeaveAllocationController,
    HolidayController,
    LoanController,
    LoanPaymentController,
    LateDeductionController,
    CalendarController,
    NotificationController
};
use App\Http\Controllers\Discipline\{
    InfractionReportController,
    DisciplinaryActionController
};

// Public
Route::get('/', fn() => redirect()->route('login'));
Route::get('/login',  [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout',[LogoutController::class,'logout'])->name('logout');

Route::get('password/request',       [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email',        [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [ResetPasswordController::class,'showResetForm'])->name('password.reset');
Route::post('password/reset',        [ResetPasswordController::class,'reset'])->name('password.update');

// Kiosk attendance
Route::get('kiosk',  [AttendanceController::class,'log'])->name('attendance.kiosk');
Route::post('kiosk', [AttendanceController::class,'logAttendance'])->name('attendance.kiosk.post');
Route::get('attendance/employee/{code}', [AttendanceController::class,'employeeInfo'])->name('attendance.employee.info');
Route::get('attendance/code/{name}',     [AttendanceController::class,'employeeCodeFromName'])->name('attendance.employee.code');

// Authenticated
Route::middleware('auth')->group(function () {

    // Dashboards
    Route::get('/dashboard',          [DashboardController::class,'index'])->name('dashboard');
    Route::get('/employee/dashboard', [EmployeeDashboardController::class,'index'])->name('dashboard.employee');

    // Storage proxy
    Route::get('storage/{path}', function ($path) {
        $file = storage_path('app/public/' . $path);
        abort_unless(file_exists($file), 404);
        return response()->file($file);
    })->where('path', '.+');

    // Announcements
    Route::resource('announcements', AnnouncementController::class)
         ->only(['index','create','store','show']);

    // Payslips
    Route::get('/payslips',                    [PayslipController::class,'index'])->name('payslips.index');
    Route::post('/payslips',                   [PayslipController::class,'store'])->name('payslips.store');
    Route::get('/payslips/{payslip}/download', [PayslipController::class,'download'])->name('payslips.download');

    // ─── Reports ────────────────────────────────────────
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/',                [ReportController::class,'index'])->name('index');
        Route::get('employees',                 [ReportController::class,'indexEmployees'])->name('employees.index');
        Route::get('employees/csv',             [ReportController::class,'exportEmployees'])->name('employees.csv');
        Route::get('employees/{employee}/pdf',  [ReportController::class,'downloadEmployeePdf'])->name('employees.pdf');
        Route::get('employees/{employee}/cert', [ReportController::class,'downloadCertificate'])->name('employees.cert');

        Route::get('attendance',  [ReportController::class,'exportAttendance'])->name('attendance');
        Route::get('payroll',     [ReportController::class,'exportPayroll'])->name('payroll');
        Route::get('payslips',    [ReportController::class,'exportPayslips'])->name('payslips');
        Route::get('performance', [ReportController::class,'exportPerformance'])->name('performance');
    });

    // Attendance CRUD
    Route::resource('attendance', AttendanceController::class)
         ->only(['index','show','destroy']);

    // Approvals
    Route::get('approvals',                   [ApprovalController::class,'index'])->name('approvals.index');
    Route::post('approvals/{t}/{id}/approve', [ApprovalController::class,'approve'])->name('approvals.approve');
    Route::delete('approvals/{t}/{id}',       [ApprovalController::class,'destroy'])->name('approvals.destroy');

    // Users & Roles
    Route::match(['patch','put'], '/users/{user}/role', [UserController::class,'updateRole'])
         ->name('users.updateRole');
    Route::resource('users', UserController::class)
         ->except(['show','update']);
    Route::get('users/{user}/password', [UserController::class,'editPassword'])->name('users.editPassword');
    Route::put('users/{user}/password', [UserController::class,'updatePassword'])->name('users.updatePassword');
    Route::delete('users/{user}',       [UserController::class,'destroy'])->name('users.destroy');

    // ─── Payroll calendar & AJAX ───────────────────────────────────────────────
    // Change name prefix here so you get payroll.calendar.* route names
    Route::prefix('payroll/calendar')
         ->name('payroll.calendar.')
         ->group(function () {
             Route::get('/',          [CalendarController::class,'index'])->name('index');
             Route::post('toggle',    [CalendarController::class,'toggleManual'])->name('toggleManual');
             Route::post('action',    [CalendarController::class,'cellAction'])->name('cellAction');
             Route::post('biometric', [CalendarController::class,'setBiometric'])->name('biometric');
             Route::delete('remove',  [CalendarController::class,'removeManual'])->name('remove');
         });

    // Core HR resources
    Route::resources([
        'departments'  => DepartmentController::class,
        'designations' => DesignationController::class,
        'schedule'     => ScheduleController::class,
        'payroll'      => PayrollController::class,
        'loans'        => LoanController::class,
    ]);

    // Leave types & allocations
    Route::resource('leave-types',       LeaveTypeController::class);
    Route::resource('leave-allocations', LeaveAllocationController::class);
    Route::resource('late-deductions',   LateDeductionController::class);

    // Employee management
    Route::resource('employees', EmployeeController::class)
         ->except(['show']);
    Route::prefix('employees')->name('employees.')->group(function () {
        Route::get('endings',         [EmployeeController::class,'endings'])->name('endings');
        Route::get('inactive',        [EmployeeController::class,'inactive'])->name('inactive');
        Route::patch('{employee}/restore',         [EmployeeController::class,'restore'])->name('restore');
        Route::patch('{employee}/regularize',      [EmployeeController::class,'regularize'])->name('regularize');
        Route::delete('{employee}/reject-probation',[EmployeeController::class,'rejectProbation'])->name('rejectProbation');
        Route::patch('{employee}/extend-term',     [EmployeeController::class,'extendTerm'])->name('extendTerm');
        Route::patch('{employee}/terminate',       [EmployeeController::class,'terminate'])->name('terminate');
        Route::patch('{employee}/extend-season',   [EmployeeController::class,'extendSeason'])->name('extendSeason');
        Route::patch('{employee}/extend-project',  [EmployeeController::class,'extendProject'])->name('extendProject');
        Route::patch('{employee}/extend-casual',   [EmployeeController::class,'extendCasual'])->name('extendCasual');
    });

    // Audit logs
    Route::get('audit-logs', [AuditLogController::class,'index'])->name('audit-logs.index');

    // Leave requests
    Route::resource('leaves', LeaveController::class)
         ->only(['index','create','store','edit','update','destroy']);

    // Performance forms & evaluations
    Route::resource('performance-forms', PerformanceFormController::class)
         ->names('performance_forms');
    Route::prefix('evaluations')
         ->name('evaluations.')
         ->controller(EvaluationController::class)
         ->group(function () {
             Route::get('/',         'index')->name('index');
             Route::get('completed', 'completed')->name('completed');
             Route::get('{form}/{employee}', 'show')->name('show');
             Route::post('{form}/{employee}','store')->name('store');
         });
    Route::prefix('my-evaluations')
         ->name('my.evaluations.')
         ->controller(EmployeeEvaluationController::class)
         ->group(function () {
             Route::get('/',           'index')->name('index');
             Route::get('{evaluation}','show')->name('show');
         });
    Route::get('performance/reports', [EvaluationController::class,'reports'])
         ->name('performance.reports');

    // Profile
    Route::get('/profile', [ProfileController::class,'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class,'update'])->name('profile.update');

    // Holidays
    Route::resource('holidays', HolidayController::class);

    // Discipline Management
    Route::prefix('discipline')
         ->name('discipline.')
         ->group(function () {
             Route::resource('infractions', InfractionReportController::class);
             Route::resource('actions', DisciplinaryActionController::class);
         });

    // Notifications
    Route::prefix('notifications')
         ->name('notifications.')
         ->group(function () {
             Route::get('/',               [NotificationController::class, 'index'])->name('index');
             Route::get('{id}',            [NotificationController::class, 'show'])->name('show');
             Route::post('{id}/mark-read', [NotificationController::class, 'markRead'])->name('markRead');
             Route::post('mark-all-read',  [NotificationController::class, 'markAllRead'])->name('markAllRead');
         });
});
