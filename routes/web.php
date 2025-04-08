<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\SidebarController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DisciplinaryController;
use App\Http\Controllers\InactiveUserController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\AttendanceController as WebAttendanceController;
use App\Http\Controllers\Api\AttendanceController as ApiAttendanceController;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => redirect()->route('login'));
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

// Redirect for forgotten password
Route::redirect('/forgot-password', '/password/reset');

Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])
    ->name('password.request');
Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])
    ->name('password.email');
Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])
    ->name('password.reset');
Route::post('password/reset', [ResetPasswordController::class, 'reset'])
    ->name('password.update');

/*
|--------------------------------------------------------------------------
| Protected Routes (Requires Authentication)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Organization Management
    Route::resources([
        'departments'  => DepartmentController::class,
        'designations' => DesignationController::class,
    ]);

    // Employee Management - use all resource routes (including create)
    Route::resource('employees', EmployeeController::class);

    Route::resources([
        'disciplinary'   => DisciplinaryController::class,
        'inactive_users' => InactiveUserController::class,
    ]);

    // Attendance & Schedule
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/store', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::post('/attendance/{id}/timeout', [AttendanceController::class, 'timeout'])->name('attendance.timeout');
    Route::get('/attendance/log', [AttendanceController::class, 'logForm'])->name('attendance.log.form');
    Route::post('/attendance/log', [AttendanceController::class, 'logAttendance'])->name('attendance.log.submit');
    Route::post('/attendance/print', [AttendanceController::class, 'printPdf'])->name('attendance.print');
    Route::resource('schedule', ScheduleController::class);

        // Payroll Routes
    Route::middleware(['auth'])->group(function () {
        Route::get('/payroll', [\App\Http\Controllers\PayrollController::class, 'index'])->name('payroll.index');
        Route::get('/payroll/{id}', [\App\Http\Controllers\PayrollController::class, 'show'])->name('payroll.show');
    });

    // Deduction Routes
    Route::middleware(['auth'])->group(function () {
        Route::get('/deductions', [\App\Http\Controllers\DeductionController::class, 'index'])->name('deductions.index');
        Route::get('/deductions/{id}/edit', [\App\Http\Controllers\DeductionController::class, 'edit'])->name('deductions.edit');
        Route::put('/deductions/{id}', [\App\Http\Controllers\DeductionController::class, 'update'])->name('deductions.update');
    });

    // Shift Management
    Route::resource('shifts', ShiftController::class);

    // Evaluation
    Route::get('/evaluation', [EvaluationController::class, 'index'])->name('evaluation.index');

    // Profile Management
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

        // Additional routes for user management
        Route::post('/bulk-action', [UserController::class, 'bulkAction'])->name('bulkAction');
        Route::put('{id}/role', [UserController::class, 'updateRole'])->name('updateRole');
        Route::put('{id}/password', [UserController::class, 'changePassword'])->name('changePassword');
        Route::post('{id}/reset-password', [UserController::class, 'resetPassword'])->name('resetPassword');

        // Role Assignment
        Route::get('{id}/assign-role', [UserController::class, 'assignRoleForm'])->name('assignRole');
        Route::post('{id}/assign-role', [UserController::class, 'assignRole'])->name('assignRole.store');
    });
});
