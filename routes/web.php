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
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\HrDashboardController;
use App\Http\Controllers\EmployeeDashboardController;

Auth::routes();

Route::middleware(['auth'])->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard')->middleware('role:admin');
    Route::get('/hr/dashboard', [HrDashboardController::class, 'index'])->name('hr.dashboard')->middleware('role:hr');
    Route::get('/employee/dashboard', [EmployeeDashboardController::class, 'index'])->name('employee.dashboard')->middleware('role:employee');
});


Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');

Route::post('/login', [LoginController::class, 'login']);

Route::get('/', function () {
    return redirect()->route('dashboard'); // Redirect to dashboard instead of home
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth')->name('dashboard');


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
