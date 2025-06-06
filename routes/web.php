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

// -----------------------------------------------------------------------------
// Public (no auth)
// -----------------------------------------------------------------------------

// Redirect root to login
Route::get('/', fn() => redirect()->route('login'));

// Login / Logout
Route::get('/login',  [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

// Password reset
Route::get('password/request',       [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email',        [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [ResetPasswordController::class,'showResetForm'])->name('password.reset');
Route::post('password/reset',        [ResetPasswordController::class,'reset'])->name('password.update');

// Attendance Kiosk & AJAX lookups
Route::get('kiosk',  [AttendanceController::class,'log'])->name('attendance.kiosk');
Route::post('kiosk', [AttendanceController::class,'logAttendance'])->name('attendance.kiosk.post');
Route::get('attendance/employee/{code}', [AttendanceController::class,'employeeInfo'])->name('attendance.employee.info');
Route::get('attendance/code/{name}',     [AttendanceController::class,'employeeCodeFromName'])->name('attendance.employee.code');

// -----------------------------------------------------------------------------
// Authenticated routes (no role middleware on Announcements)
// -----------------------------------------------------------------------------
Route::middleware('auth')->group(function () {

    // DASHBOARDS
    Route::get('/dashboard',          [DashboardController::class,         'index'])->name('dashboard');
    Route::get('/employee/dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard.employee');

    // Serve storage files / workaround for Windows symlinks
    Route::get('storage/{path}', function ($path) {
        $file = storage_path('app/public/' . $path);
        if (! file_exists($file)) {
            abort(404);
        }
        return response()->file($file);
    })->where('path', '.+');

    // ANNOUNCEMENTS
    Route::resource('announcements', AnnouncementController::class)
         ->only(['index','create','store','show']);

    // PAYSLIPS
    Route::get('/payslips',                    [PayslipController::class,'index'])->name('payslips.index');
    Route::post('/payslips',                   [PayslipController::class,'store'])->name('payslips.store');
    Route::get('/payslips/{payslip}/download', [PayslipController::class,'download'])->name('payslips.download');

    // REPORTS
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/',         [ReportController::class,'index'])->name('index');
        Route::get('employees', [ReportController::class,'exportEmployees'])->name('employees');
        // ... other exports ...
    });

    // ATTENDANCE LIST & DELETE (admin)
    Route::resource('attendance', AttendanceController::class)->only(['index','destroy']);

    // APPROVALS
    Route::get   ('approvals',                  [ApprovalController::class,'index'])->name('approvals.index');
    Route::post  ('approvals/{t}/{id}/approve', [ApprovalController::class,'approve'])->name('approvals.approve');
    Route::delete('approvals/{t}/{id}',         [ApprovalController::class,'destroy'])->name('approvals.destroy');

    // INLINE AJAX ROLE UPDATE
    Route::match(['patch','put'], '/users/{user}/role', [UserController::class,'updateRole'])
         ->name('users.updateRole');

    // USER MANAGEMENT
    Route::resource('users', UserController::class)->except(['show','update']);
    Route::get   ('users/{user}/password', [UserController::class,'editPassword'])->name('users.editPassword');
    Route::put   ('users/{user}/password', [UserController::class,'updatePassword'])->name('users.updatePassword');
    Route::delete('users/{user}',          [UserController::class,'destroy'])->name('users.destroy');

    // CORE HR MODULES
    Route::resources([
        'departments'  => DepartmentController::class,
        'designations' => DesignationController::class,
        'deductions'   => DeductionController::class,
        'employees'    => EmployeeController::class,
        'payroll'      => PayrollController::class,
        'schedule'     => ScheduleController::class,
    ]);

    // New: Mark an employee as “Regular” once probation ends
    Route::patch('employees/{employee}/regularize', [EmployeeController::class, 'regularize'])
         ->name('employees.regularize')
         ->middleware('role:hr');  // only HR can do this

    // AUDIT LOGS
    Route::get('audit-logs', [AuditLogController::class,'index'])->name('audit-logs.index');

    // LEAVE REQUESTS
    Route::resource('leaves', LeaveController::class)
         ->only(['index','create','store','edit','update','destroy']);

    // PERFORMANCE EVALUATION
    Route::prefix('performance-forms')
         ->name('performance.forms.')->group(function(){
            Route::get('/',          [PerformanceFormController::class,'index'])->name('index');
            Route::get('create',     [PerformanceFormController::class,'create'])->name('create');
            Route::post('/',         [PerformanceFormController::class,'store'])->name('store');
            Route::get('{form}/edit',[PerformanceFormController::class,'edit'])->name('edit');
            Route::put('{form}',     [PerformanceFormController::class,'update'])->name('update');
            Route::delete('{form}',  [PerformanceFormController::class,'destroy'])->name('destroy');
         });

    Route::prefix('evaluations')
         ->name('evaluations.')->group(function(){
            Route::get('/',                  [EvaluationController::class,'index'])->name('index');
            Route::get('{form}/{employee}',  [EvaluationController::class,'show'])->name('show');
            Route::post('{form}/{employee}', [EvaluationController::class,'store'])->name('store');
         });

    Route::prefix('my-evaluations')
         ->name('my.evaluations.')->group(function(){
            Route::get('/',            [EmployeeEvaluationController::class,'index'])->name('index');
            Route::get('{evaluation}', [EmployeeEvaluationController::class,'show'])->name('show');
         });

    Route::get('performance/reports', [EvaluationController::class,'reports'])
         ->name('performance.reports');

    // PROFILE & DEBUG
    Route::get('profile',  [ProfileController::class,'index'])->name('profile.index');
    Route::post('profile', [ProfileController::class,'update'])->name('profile.update');
});
