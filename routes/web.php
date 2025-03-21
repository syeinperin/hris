<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SidebarController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DisciplinaryController;
use App\Http\Controllers\InactiveUserController;
use App\Http\Controllers\DesignationController;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Auth::routes();

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');

/*
|--------------------------------------------------------------------------
| Protected Routes (Requires Authentication)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
     /*
    |--------------------------------------------------------------------------
    | Organization Management
    |--------------------------------------------------------------------------
    */
    Route::resource('departments', DepartmentController::class);
    Route::resource('designations', DesignationController::class);

    /*
    |--------------------------------------------------------------------------
    | Employee Management
    |--------------------------------------------------------------------------
    */
    Route::resource('employees', EmployeeController::class);
    Route::resource('disciplinary', DisciplinaryController::class);
    Route::resource('inactive_users', InactiveUserController::class);

    /*
    |--------------------------------------------------------------------------
    | Attendance Management
    |--------------------------------------------------------------------------
    */
    Route::prefix('attendance')->middleware(['auth', 'role:admin'])->group(function () {
        Route::get('/', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('/{id}', [AttendanceController::class, 'show'])->name('attendance.show');
        Route::get('/import', [AttendanceController::class, 'importForm'])->name('attendance.importForm');
        Route::post('/import', [AttendanceController::class, 'import'])->name('attendance.import');
        Route::get('/report', [AttendanceController::class, 'report'])->name('attendance.report');
    });    

    /*
    |--------------------------------------------------------------------------
    | Finance Management (Payroll, Payslip, Loans)
    |--------------------------------------------------------------------------
    */
    Route::prefix('finance')->group(function () {
        Route::get('/payroll', [FinanceController::class, 'payroll'])->name('finance.payroll');
        Route::get('/payslip/generate', [FinanceController::class, 'generatePayslip'])->name('finance.payslip.generate');
        Route::get('/payslip/report', [FinanceController::class, 'payslipReport'])->name('finance.payslip.report');
        Route::get('/loans', [FinanceController::class, 'loans'])->name('finance.loans');
    });

    /*
    |--------------------------------------------------------------------------
    | Shift Management
    |--------------------------------------------------------------------------
    */
    Route::resource('shifts', ShiftController::class);
    

    /*
    |--------------------------------------------------------------------------
    | Evaluation Management
    |--------------------------------------------------------------------------
    */
    Route::get('/evaluation', [EvaluationController::class, 'index'])->name('evaluation.index');

    /*
    |--------------------------------------------------------------------------
    | Profile Management
    |--------------------------------------------------------------------------
    */
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');

    /*
    |--------------------------------------------------------------------------
    | Role-Based Dashboards
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin/dashboard', [DashboardController::class, 'admin'])->name('admin.dashboard');
    });    

    Route::middleware(['role:hr'])->group(function () {
        Route::get('/hr/dashboard', [DashboardController::class, 'hr'])->name('hr.dashboard');
    });

    Route::middleware(['role:timekeeper'])->group(function () {
        Route::get('/timekeeper/dashboard', [DashboardController::class, 'timekeeper'])->name('timekeeper.dashboard');
    });

    Route::middleware(['role:supervisor'])->group(function () {
        Route::get('/supervisor/dashboard', [DashboardController::class, 'supervisor'])->name('supervisor.dashboard');
    });

    Route::middleware(['role:user'])->group(function () {
        Route::get('/user/dashboard', [DashboardController::class, 'user'])->name('user.dashboard');
    });


    Route::resource('reports', ReportController::class);
});
