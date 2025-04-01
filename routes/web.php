<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
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
use App\Http\Controllers\UserController;
use App\Http\Controllers\AttendanceController as WebAttendanceController;
use App\Http\Controllers\Api\AttendanceController as ApiAttendanceController;
/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Auth::routes();

Route::get('/', fn () => redirect()->route('login'));
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');
Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');

/*
|--------------------------------------------------------------------------
| Protected Routes (Requires Authentication)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // ✅ Shared Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ✅ Organization Management
    Route::resources([
        'departments' => DepartmentController::class,
        'designations' => DesignationController::class,
    ]);

    // ✅ Employee Management
    Route::resources([
        'employees' => EmployeeController::class,
        'disciplinary' => DisciplinaryController::class,
        'inactive_users' => InactiveUserController::class,
    ]);

    // Web routes for attendance (returns views)
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/store', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::post('/attendance/{id}/timeout', [AttendanceController::class, 'timeout'])->name('attendance.timeout');

    // API route for attendance logging
    Route::post('/attendance/log', [ApiAttendanceController::class, 'logAttendance'])->name('attendance.log');  

    // Finance Management
    Route::prefix('finance')->group(function () {
        Route::get('/payroll', [FinanceController::class, 'payroll'])->name('finance.payroll');
        Route::get('/payslip/generate', [FinanceController::class, 'generatePayslip'])->name('finance.payslip.generate');
        Route::get('/payslip/report', [FinanceController::class, 'payslipReport'])->name('finance.payslip.report');
        Route::get('/loans', [FinanceController::class, 'loans'])->name('finance.loans');
    });

    // Shift Management
    Route::resource('shifts', ShiftController::class);

    // Evaluation
    Route::get('/evaluation', [EvaluationController::class, 'index'])->name('evaluation.index');

    // Profile
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');

    // Reports
    Route::resource('reports', ReportController::class);

    // User Management
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('{id}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('{id}', [UserController::class, 'update'])->name('update');
        Route::delete('{id}', [UserController::class, 'destroy'])->name('destroy');

        // Role Assignment
        Route::get('{id}/assign-role', [UserController::class, 'assignRoleForm'])->name('assignRole');
        Route::post('{id}/assign-role', [UserController::class, 'assignRole'])->name('assignRole.store');
    });
});
