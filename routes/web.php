<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\PayslipController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ConcernsController;




// Attendance Routes
Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.list');
Route::get('/attendance/import', [AttendanceController::class, 'import'])->name('attendance.import');
Route::get('/attendance/report', [AttendanceController::class, 'report'])->name('attendance.report');

// Shift Routes
Route::get('/shifts', [ShiftController::class, 'index'])->name('shift.index');

// Payroll Routes
Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.list');

// Payslip Routes
Route::get('/payslip/generate', [PayslipController::class, 'generate'])->name('payslip.generate');
Route::get('/payslip/report', [PayslipController::class, 'report'])->name('payslip.report');

// Employee Routes
Route::get('/employee/info', [EmployeeController::class, 'info'])->name('employee.info');
Route::get('/employee/manage', [EmployeeController::class, 'crud'])->name('employee.crud');
Route::get('/employee/profile', [EmployeeController::class, 'profile'])->name('employee.profile');
Route::get('/employee/concerns', [EmployeeController::class, 'concerns'])->name('employee.concerns');

// Department Routes
Route::get('/departments', [DepartmentController::class, 'index'])->name('department.index');

// Evaluation Routes
Route::get('/evaluation', [EvaluationController::class, 'index'])->name('evaluation.index');

// Role and User Management
Route::get('/roles', [RoleController::class, 'manage'])->name('roles.management');
Route::get('/users', [UserController::class, 'crud'])->name('users.crud');
Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');

// Supervisor Functions
Route::get('/evaluation', [EvaluationController::class, 'index'])->name('evaluation.index');
Route::get('/employee/status', [EmployeeController::class, 'status'])->name('employee.status');
Route::get('/attendance/reports', [AttendanceController::class, 'reports'])->name('attendance.reports');

// User Functions
Route::get('/payslip/generate', [PayslipController::class, 'generate'])->name('payslip.generate');
Route::get('/employee/profile', [ProfileController::class, 'index'])->name('employee.profile');
Route::get('/employee/concerns', [ConcernsController::class, 'index'])->name('employee.concerns');

// Admin Functions
Route::get('/roles/management', [RoleController::class, 'index'])->name('roles.management');
Route::get('/users/crud', [UserController::class, 'index'])->name('users.crud');



Auth::routes();

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');

Route::post('/login', [LoginController::class, 'login']);


Route::get('/', function () {
    return redirect()->route('login'); // Redirect to login page
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');


// Forgot Password Route
Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');

Route::get('/dashboard', function () {
    return view('dashboard'); // Change this if your dashboard view is different
})->name('dashboard');

Route::get('/roles/{role}', [RoleController::class, 'show']);

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', 'AdminController@index');
});

Route::middleware(['auth', 'role:hr'])->group(function () {
    Route::get('/hr/dashboard', 'HRController@index');
});

Route::middleware(['auth', 'role:timekeeper'])->group(function () {
    Route::get('/timekeeper/dashboard', 'TimekeeperController@index');
});

Route::middleware(['auth', 'role:supervisor'])->group(function () {
    Route::get('/supervisor/dashboard', 'SupervisorController@index');
});

Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/user/dashboard', 'UserController@index');
});


    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/login'); // Redirect to login page
    })->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth')->name('dashboard');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
