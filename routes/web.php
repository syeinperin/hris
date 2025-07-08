<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeDashboardController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\PayslipController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\DeductionController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\PerformanceFormController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\EmployeeEvaluationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\LeaveAllocationController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\LoanPaymentController;
use App\Http\Controllers\CalendarController;

// -----------------------------------------------------------------------------
// Public (no auth)
// -----------------------------------------------------------------------------
Route::get('/', fn() => redirect()->route('login'));

Route::get('/login',  [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout',[LogoutController::class,'logout'])->name('logout');

Route::get('password/request',       [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email',        [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [ResetPasswordController::class,'showResetForm'])->name('password.reset');
Route::post('password/reset',        [ResetPasswordController::class,'reset'])->name('password.update');

Route::get('kiosk',  [AttendanceController::class,'log'])->name('attendance.kiosk');
Route::post('kiosk', [AttendanceController::class,'logAttendance'])->name('attendance.kiosk.post');
Route::get('attendance/employee/{code}', [AttendanceController::class,'employeeInfo'])->name('attendance.employee.info');
Route::get('attendance/code/{name}',     [AttendanceController::class,'employeeCodeFromName'])->name('attendance.employee.code');

// -----------------------------------------------------------------------------
// Authenticated routes
// -----------------------------------------------------------------------------
Route::middleware('auth')->group(function () {

    // Dashboards
    Route::get('/dashboard',          [DashboardController::class,         'index'])->name('dashboard');
    Route::get('/employee/dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard.employee');

    // Storage workaround
    Route::get('storage/{path}', function ($path) {
        $file = storage_path('app/public/' . $path);
        if (! file_exists($file)) abort(404);
        return response()->file($file);
    })->where('path', '.+');

    // Announcements
    Route::resource('announcements', AnnouncementController::class)
         ->only(['index','create','store','show']);

    // Payslips
    Route::get('/payslips',                    [PayslipController::class,'index'])->name('payslips.index');
    Route::post('/payslips',                   [PayslipController::class,'store'])->name('payslips.store');
    Route::get('/payslips/{payslip}/download', [PayslipController::class,'download'])->name('payslips.download');

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/',         [ReportController::class,'index'])->name('index');
        Route::get('employees', [ReportController::class,'exportEmployees'])->name('employees');
    });

    // Attendance
    Route::resource('attendance', AttendanceController::class)
         ->only(['index','destroy','show']);

    // Approvals
    Route::get   ('approvals',                  [ApprovalController::class,'index'])->name('approvals.index');
    Route::post  ('approvals/{t}/{id}/approve', [ApprovalController::class,'approve'])->name('approvals.approve');
    Route::delete('approvals/{t}/{id}',         [ApprovalController::class,'destroy'])->name('approvals.destroy');

    // User management & roles
    Route::match(['patch','put'], '/users/{user}/role', [UserController::class,'updateRole'])
         ->name('users.updateRole');
    Route::resource('users', UserController::class)->except(['show','update']);
    Route::get   ('users/{user}/password', [UserController::class,'editPassword'])->name('users.editPassword');
    Route::put   ('users/{user}/password', [UserController::class,'updatePassword'])->name('users.updatePassword');
    Route::delete('users/{user}',          [UserController::class,'destroy'])->name('users.destroy');

    // Payroll calendar & AJAX
    Route::get('payroll/calendar',            [CalendarController::class,'index'])->name('payroll.calendar');
    Route::post('payroll/calendar/toggle',    [CalendarController::class,'toggleManual'])->name('calendar.toggleManual');
    Route::post('payroll/calendar/action',    [CalendarController::class,'cellAction'])->name('calendar.cellAction');
    Route::post('payroll/calendar/biometric', [CalendarController::class,'setBiometric'])->name('calendar.biometric');
    Route::delete('payroll/calendar/remove',  [CalendarController::class,'removeManual'])->name('calendar.remove');

    // Core HR modules
    Route::resources([
        'departments'   => DepartmentController::class,
        'designations'  => DesignationController::class,
        'deductions'    => DeductionController::class,
        'schedule'      => ScheduleController::class,
        'payroll'       => PayrollController::class,
        'loans'         => LoanController::class,
    ]);

    // Leave types & allocations
    Route::resource('leave-types',       LeaveTypeController::class);
    Route::resource('leave-allocations', LeaveAllocationController::class);
    Route::resource('late-deductions',   LateDeductionController::class);

    // Employee resource & extra actions
    Route::resource('employees', EmployeeController::class)->except(['show']);
    Route::get('employees/endings',      [EmployeeController::class, 'endings'])->name('employees.endings');
    Route::get('employees/inactive',     [EmployeeController::class, 'inactive'])->name('employees.inactive');
    Route::patch('employees/{employee}/restore',        [EmployeeController::class,'restore'])->name('employees.restore');
    Route::patch('employees/{employee}/regularize',     [EmployeeController::class,'regularize'])->name('employees.regularize');
    Route::delete('employees/{employee}/reject-probation',[EmployeeController::class,'rejectProbation'])->name('employees.rejectProbation');
    Route::patch('employees/{employee}/extend-term',    [EmployeeController::class,'extendTerm'])->name('employees.extendTerm');
    Route::patch('employees/{employee}/terminate',      [EmployeeController::class,'terminate'])->name('employees.terminate');
    Route::patch('employees/{employee}/extend-season',  [EmployeeController::class,'extendSeason'])->name('employees.extendSeason');
    Route::patch('employees/{employee}/extend-project', [EmployeeController::class,'extendProject'])->name('employees.extendProject');
    Route::patch('employees/{employee}/extend-casual',  [EmployeeController::class,'extendCasual'])->name('employees.extendCasual');

    // Audit logs
    Route::get('audit-logs', [AuditLogController::class,'index'])->name('audit-logs.index');

    // Leave requests
    Route::resource('leaves', LeaveController::class)
         ->only(['index','create','store','edit','update','destroy']);

 Route::resource('performance-forms', PerformanceFormController::class)
         ->names('performance_forms');

    // -------------------------------------------------------------------------
    // Evaluations (for supervisors)
    // -------------------------------------------------------------------------
    Route::prefix('evaluations')
         ->name('evaluations.')
         ->controller(EvaluationController::class)
         ->group(function () {
             // Fill/Pending list
             Route::get('/',        'index')->name('index');
             // Completed list
             Route::get('completed','completed')->name('completed');
             // Show the form for a given form + employee
             Route::get('{form}/{employee}','show')->name('show');
             // Submit evaluation
             Route::post('{form}/{employee}','store')->name('store');
         });

    // -------------------------------------------------------------------------
    // My Evaluations (for employees)
    // -------------------------------------------------------------------------
    Route::prefix('my-evaluations')
         ->name('my.evaluations.')
         ->controller(EmployeeEvaluationController::class)
         ->group(function () {
             Route::get('/',        'index')->name('index');
             Route::get('{evaluation}','show')->name('show');
         });

// -------------------------------------------------------------------------
// Performance Reports
// -------------------------------------------------------------------------
Route::get('performance/reports', [EvaluationController::class,'reports'])
     ->name('performance.reports');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])
         ->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])
         ->name('profile.update');

    // Holidays
    Route::resource('holidays', HolidayController::class);
});
