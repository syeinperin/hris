<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    Auth\LoginController,
    Auth\ForgotPasswordController,
    Auth\ResetPasswordController,
    Auth\LogoutController,
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
    DeductionController,
    EmployeeController,
    PayrollController,
    ScheduleController,
    PerformanceEvaluationController,
    PerformancePlanController,
    ProfileController,
    SidebarDebugController
};

/*
|--------------------------------------------------------------------------
| Public (no auth)
|--------------------------------------------------------------------------
*/
Route::get('/', fn() => redirect()->route('login'));
Route::get('/login',  [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

Route::get('password/request',       [ForgotPasswordController::class, 'showLinkRequestForm'])
     ->name('password.request');
Route::post('password/email',        [ForgotPasswordController::class, 'sendResetLinkEmail'])
     ->name('password.email');
Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])
     ->name('password.reset');
Route::post('password/reset',        [ResetPasswordController::class, 'reset'])
     ->name('password.update');

// Kiosk & AJAX lookups
Route::get('kiosk',  [AttendanceController::class, 'log'])->name('attendance.kiosk');
Route::post('kiosk', [AttendanceController::class, 'logAttendance'])->name('attendance.kiosk.post');
Route::get('attendance/employee/{code}', [AttendanceController::class, 'employeeInfo'])
     ->name('attendance.employee.info');
Route::get('attendance/code/{name}',     [AttendanceController::class, 'employeeCode'])
     ->name('attendance.employee.code');

/*
|--------------------------------------------------------------------------
| Authenticated
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    //
    // ─── ADMIN & EMPLOYEE DASHBOARDS ──────────────────────────────────────
    //
    // Admin
    Route::get('/dashboard', [DashboardController::class, 'index'])
         ->name('dashboard');

    // Employee
    Route::get('/employee/dashboard', [EmployeeDashboardController::class, 'index'])
         ->name('dashboard.employee');

    //
    // ─── SELF-SERVICE: LEAVES & PAYSLIPS ─────────────────────────────────
    //
    Route::get('/leaves',        [LeaveController::class, 'index'])->name('leaves.index');
    Route::get('/leaves/create', [LeaveController::class, 'create'])->name('leaves.create');
    Route::post('/leaves',       [LeaveController::class, 'store'])->name('leaves.store');
    Route::get('/leaves/{leave}/edit', [LeaveController::class, 'edit'])->name('leaves.edit');
    Route::put('/leaves/{leave}',      [LeaveController::class, 'update'])->name('leaves.update');
    Route::delete('/leaves/{leave}',   [LeaveController::class, 'destroy'])->name('leaves.destroy');

    Route::get('/payslips',      [PayslipController::class, 'index'])->name('payslips.index');
    Route::post('/payslips',     [PayslipController::class, 'store'])->name('payslips.store');
    Route::get('/payslips/{payslip}/download', [PayslipController::class, 'download'])
         ->name('payslips.download');

    //
    // ─── REPORTS ──────────────────────────────────────────────────────────
    //
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/',           [ReportController::class, 'index'])->name('index');
        Route::get('employees',   [ReportController::class, 'exportEmployees'])->name('employees');
        Route::get('attendance',  [ReportController::class, 'exportAttendance'])->name('attendance');
        Route::get('payroll',     [ReportController::class, 'exportPayroll'])->name('payroll');
        Route::get('payslips',    [ReportController::class, 'exportPayslips'])->name('payslips');
        Route::get('performance', [ReportController::class, 'exportPerformance'])->name('performance');
    });

    //
    // ─── ATTENDANCE LIST & DELETE ────────────────────────────────────────
    //
    Route::resource('attendance', AttendanceController::class)
         ->only(['index','destroy']);

    //
    // ─── APPROVALS ───────────────────────────────────────────────────────
    //
    Route::get('approvals', [ApprovalController::class,'index'])->name('approvals.index');
    Route::post('approvals/{t}/{id}/approve', [ApprovalController::class,'approve'])
         ->name('approvals.approve');
    Route::delete('approvals/{t}/{id}', [ApprovalController::class,'destroy'])
         ->name('approvals.destroy');

    //
    // ─── USERS & ROLES ──────────────────────────────────────────────────
    //
    Route::put('users/{user}/role',       [UserController::class,'updateRole'])
         ->name('users.updateRole');
    Route::resource('users', UserController::class)
         ->except(['show','update']);
    Route::get('users/{user}/password', [UserController::class,'editPassword'])
         ->name('users.editPassword');
    Route::put('users/{user}/password', [UserController::class,'updatePassword'])
         ->name('users.updatePassword');

    //
    // ─── CORE HR MODULES ────────────────────────────────────────────────
    //
    Route::resources([
        'departments'  => DepartmentController::class,
        'designations' => DesignationController::class,
        'deductions'   => DeductionController::class,
        'employees'    => EmployeeController::class,
        'payroll'      => PayrollController::class,
        'schedule'     => ScheduleController::class,
        'evaluation'   => PerformanceEvaluationController::class,
    ]);

    //
    // ─── PERFORMANCE PLANS ──────────────────────────────────────────────
    //
    Route::resource('plans', PerformancePlanController::class)
         ->except(['show']);

    //
    // ─── PROFILE & DEBUG ────────────────────────────────────────────────
    //
    Route::get('profile',       [ProfileController::class, 'index'])->name('profile');
    Route::post('profile',      [ProfileController::class, 'update'])->name('profile.update');
    Route::get('sidebar-debug', [SidebarDebugController::class,'index'])
         ->name('sidebar.debug');
});
