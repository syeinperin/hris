<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage; // ← needed by files route
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
    FaceRecognitionController,
    NotificationController,
    DisciplinaryActionController
};

// ───────────────────────────────────────────────────────────────────────────────
// Public routes
// ───────────────────────────────────────────────────────────────────────────────

Route::get('/', fn () => redirect()->route('login'));

Route::get('/login',  [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

Route::get('password/request',       [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email',        [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [ResetPasswordController::class,   'showResetForm'])->name('password.reset');
Route::post('password/reset',        [ResetPasswordController::class,   'reset'])->name('password.update');

// Kiosk (manual) attendance
Route::get('kiosk',                      [AttendanceController::class, 'log'])->name('attendance.kiosk');
Route::post('kiosk',                     [AttendanceController::class, 'logAttendance'])->name('attendance.kiosk.post');
Route::get('attendance/employee/{code}', [AttendanceController::class, 'employeeInfo'])->name('attendance.employee.info');
Route::get('attendance/code/{name}',     [AttendanceController::class, 'employeeCodeFromName'])->name('attendance.employee.code');

// NEW: Face Attendance Kiosk (public, no auth)
Route::get('/kiosk/face',        [FaceRecognitionController::class, 'kiosk'])->name('kiosk.face');
Route::post('/kiosk/face/match', [FaceRecognitionController::class, 'match'])->name('kiosk.face.match');

// Public file server for storage (read-only)
Route::get('files/{path}', function (string $path) {
    $disk = Storage::disk('public');
    abort_unless($disk->exists($path), 404);

    $mime   = $disk->mimeType($path) ?? 'application/octet-stream';
    $stream = $disk->readStream($path);

    return response()->stream(function () use ($stream) {
        fpassthru($stream);
    }, 200, [
        'Content-Type'        => $mime,
        'Cache-Control'       => 'public, max-age=31536000, immutable',
        'Content-Disposition' => 'inline',
    ]);
})->where('path', '.*')->name('public.files');

// Public alias for kiosk Time In/Out submissions (used by face kiosk, safe to keep)
Route::post('/attendance/log', [AttendanceController::class, 'logAttendance'])
    ->name('attendance.logAttendance');

// ───────────────────────────────────────────────────────────────────────────────
// Authenticated area
// ───────────────────────────────────────────────────────────────────────────────

Route::middleware('auth')->group(function () {

    // Dashboards
    Route::get('/dashboard',          [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/employee/dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard.employee');

    // Announcements
    Route::resource('announcements', AnnouncementController::class);

    // Payslips (self-service)
    Route::get('/payslips',                    [PayslipController::class, 'index'])->name('payslips.index');
    Route::post('/payslips',                   [PayslipController::class, 'store'])->name('payslips.store');
    Route::get('/payslips/{payslip}/download', [PayslipController::class, 'download'])->name('payslips.download');

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/',                        [ReportController::class, 'index'])->name('index');

        // Employees
        Route::get('employees',                [ReportController::class, 'indexEmployees'])->name('employees.index');
        Route::get('employees/csv',            [ReportController::class, 'exportEmployees'])->name('employees.csv');
        Route::get('employees/{employee}/pdf', [ReportController::class, 'downloadEmployeePdf'])->name('employees.pdf');
        Route::get('employees/{employee}/cert',[ReportController::class, 'downloadCertificate'])->name('employees.cert');

        // Attendance / Payroll / Payslips (existing)
        Route::get('attendance',               [ReportController::class, 'exportAttendance'])->name('attendance');
        Route::get('payroll',                  [ReportController::class, 'exportPayroll'])->name('payroll');
        Route::get('payslips',                 [ReportController::class, 'exportPayslips'])->name('payslips');

        // NEW → Individual employee payslip PDF for a date range
        Route::get('payslips/{employee}/pdf',  [ReportController::class, 'employeePayslipPdf'])->name('payslips.employee.pdf');

        // (optional admin list you already had)
        Route::get('payslips/list',            [PayrollController::class, 'reportPayslips'])->name('payslips.list');
        Route::get('payslips/{employee}/download', [PayrollController::class, 'downloadPayslipRange'])->name('payslips.employee.download');

        // Performance (page + CSV)
        Route::get('performance',              [ReportController::class, 'performanceIndex'])->name('performance');
        Route::get('performance/csv',          [ReportController::class, 'exportPerformance'])->name('performance.csv');

        // Violations / Suspensions CSV
        Route::get('discipline/csv',           [ReportController::class, 'exportDiscipline'])->name('discipline.csv');
    });

    // Attendance (read-only + delete)
    Route::resource('attendance', AttendanceController::class)->only(['index', 'show', 'destroy']);

    // Approvals
    Route::get('approvals',                      [ApprovalController::class, 'index'])->name('approvals.index');
    Route::post('approvals/{t}/{id}/approve',    [ApprovalController::class, 'approve'])->name('approvals.approve');
    Route::delete('approvals/{t}/{id}',          [ApprovalController::class, 'destroy'])->name('approvals.destroy');

    // Users & Roles
    Route::match(['patch','put'], '/users/{user}/role', [UserController::class, 'updateRole'])->name('users.updateRole');
    Route::resource('users', UserController::class)->except(['show', 'update']);
    Route::get('users/{user}/password',  [UserController::class, 'editPassword'])->name('users.editPassword');
    Route::put('users/{user}/password',  [UserController::class, 'updatePassword'])->name('users.updatePassword');
    Route::delete('users/{user}',        [UserController::class, 'destroy'])->name('users.destroy');

    // Payroll calendar & AJAX
    Route::prefix('payroll/calendar')->name('payroll.calendar.')->group(function () {
        Route::get('/',          [CalendarController::class, 'index'])->name('index');
        Route::post('toggle',    [CalendarController::class, 'toggleManual'])->name('toggleManual');
        // Route::post('action', [CalendarController::class, 'cellAction'])->name('cellAction'); // ← removed (no method)
        Route::post('biometric', [CalendarController::class, 'setBiometric'])->name('biometric');
        Route::delete('remove',  [CalendarController::class, 'removeManual'])->name('remove');
    });

    // Manual Payroll (place BEFORE payroll resource)
    Route::get('payroll/manual',          [PayrollController::class, 'manual'])->name('payroll.manual');
    Route::post('payroll/manual',         [PayrollController::class, 'manualStore'])->name('payroll.manual.store');
    Route::get('payroll/manual/employee', [PayrollController::class, 'employeeLookup'])->name('payroll.manual.employee');

    // Core HR resources
    Route::resources([
        'departments'  => DepartmentController::class,
        'designations' => DesignationController::class,
        'payroll'      => PayrollController::class, // keep AFTER manual routes
        'loans'        => LoanController::class,
    ]);

    // Schedule
    Route::post('/schedule/rest-day/all', [ScheduleController::class, 'setAllRestDays'])->name('schedule.restday.all');
    Route::resource('schedule', ScheduleController::class)->only(['index', 'store', 'update', 'destroy']);

    // Leave types & allocations & late deductions
    Route::resource('leave-types',       LeaveTypeController::class);
    Route::resource('leave-allocations', LeaveAllocationController::class);
    Route::resource('late-deductions',   LateDeductionController::class);

    // Employees
    Route::resource('employees', EmployeeController::class)->except(['show']);
    Route::prefix('employees')->name('employees.')->group(function () {
        Route::get('endings',                     [EmployeeController::class, 'endings'])->name('endings');
        Route::get('inactive',                    [EmployeeController::class, 'inactive'])->name('inactive');
        Route::patch('{employee}/restore',        [EmployeeController::class, 'restore'])->name('restore');
        Route::patch('{employee}/regularize',     [EmployeeController::class, 'regularize'])->name('regularize');
        Route::delete('{employee}/reject-probation', [EmployeeController::class, 'rejectProbation'])->name('rejectProbation');
        Route::patch('{employee}/extend-term',    [EmployeeController::class, 'extendTerm'])->name('extendTerm');
        Route::patch('{employee}/terminate',      [EmployeeController::class, 'terminate'])->name('terminate');
        Route::patch('{employee}/extend-season',  [EmployeeController::class, 'extendSeason'])->name('extendSeason');
        Route::patch('{employee}/extend-project', [EmployeeController::class, 'extendProject'])->name('extendProject');
        Route::patch('{employee}/extend-casual',  [EmployeeController::class, 'extendCasual'])->name('extendCasual');
    });

    // Audit logs
    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

    // Leave requests
    Route::resource('leaves', LeaveController::class)->only(['index','create','store','edit','update','destroy']);

    // Profile / Settings
    Route::get('/settings', [ProfileController::class, 'edit'])->name('settings');
    Route::put('/settings', [ProfileController::class, 'update'])->name('settings.update');
    Route::get('/profile',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile',  [ProfileController::class, 'update'])->name('profile.update');

    // Holidays
    Route::resource('holidays', HolidayController::class);

    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/',               [NotificationController::class, 'index'])->name('index');
        Route::get('{id}',            [NotificationController::class, 'show'])->name('show');
        Route::post('{id}/mark-read', [NotificationController::class, 'markRead'])->name('markRead');
        Route::post('mark-all-read',  [NotificationController::class, 'markAllRead'])->name('markAllRead');
    });

    // Internal Face pages (optional inside app)
    Route::get('/face', [FaceRecognitionController::class, 'index'])->name('face.index');
    Route::get('/face/enroll', [FaceRecognitionController::class, 'enroll'])->name('face.enroll');
    Route::post('/face/enroll', [FaceRecognitionController::class, 'enrollStore'])->name('face.enroll.store');
    Route::delete('/face/templates/{template}', [FaceRecognitionController::class, 'destroy'])->name('face.templates.destroy');

    Route::get('/face/attendance', [FaceRecognitionController::class, 'attendance'])->name('face.attendance');
    Route::post('/face/match',     [FaceRecognitionController::class, 'match'])->name('face.match');

    // LOAN ALIASES under /employee (adds employee.loans.* names)
    Route::prefix('employee')->name('employee.')->group(function () {
        Route::get('loans',               [LoanController::class, 'index'])->name('loans.index');
        Route::get('loans/{loan}/edit',   [LoanController::class, 'edit'])->name('loans.edit');
        Route::put('loans/{loan}',        [LoanController::class, 'update'])->name('loans.update');
    });
});

// === Performance Evaluation Routes ===
Route::middleware(['auth'])->group(function () {
    Route::get("/evaluations", [\App\Http\Controllers\PerformanceEvaluationController::class,"index"])->name("evaluations.index");
    Route::get("/evaluations/{evaluation}", [\App\Http\Controllers\PerformanceEvaluationController::class,"show"])->name("evaluations.show");
    Route::get("/evaluations/{evaluation}/edit", [\App\Http\Controllers\PerformanceEvaluationController::class,"edit"])->name("evaluations.edit");
    Route::post("/evaluations", [\App\Http\Controllers\PerformanceEvaluationController::class,"store"])->name("evaluations.store");
    Route::put("/evaluations/{evaluation}", [\App\Http\Controllers\PerformanceEvaluationController::class,"update"])->name("evaluations.update");
    Route::delete("/evaluations/{evaluation}", [\App\Http\Controllers\PerformanceEvaluationController::class,"destroy"])->name("evaluations.destroy");

    Route::get("/my-evaluations", [\App\Http\Controllers\EmployeeEvaluationController::class,"index"])->name("my.evaluations.index");
    Route::get("/my-evaluations/{evaluation}", [\App\Http\Controllers\EmployeeEvaluationController::class,"show"])->name("my.evaluations.show");
});

// Discipline (kept under auth)
Route::middleware('auth')->group(function () {
    // Current routes
    Route::get('/discipline', [DisciplinaryActionController::class, 'index'])->name('discipline.index');
    Route::get('/discipline/create', [DisciplinaryActionController::class, 'create'])->name('discipline.create');
    Route::post('/discipline', [DisciplinaryActionController::class, 'store'])->name('discipline.store');
    Route::put('/discipline/{action}/resolve', [DisciplinaryActionController::class, 'resolve'])->name('discipline.resolve');
    Route::delete('/discipline/{action}', [DisciplinaryActionController::class, 'destroy'])->name('discipline.destroy');
    Route::get('/discipline/{action}/pdf', [DisciplinaryActionController::class, 'pdf'])->name('discipline.pdf');

    // ── Legacy aliases so old links like discipline.infractions.index still work
    Route::get('/discipline/infractions', [DisciplinaryActionController::class, 'index'])
        ->name('discipline.infractions.index');

    Route::get('/discipline/infractions/create', [DisciplinaryActionController::class, 'create'])
        ->name('discipline.infractions.create');

    Route::post('/discipline/infractions', [DisciplinaryActionController::class, 'store'])
        ->name('discipline.infractions.store');

    Route::put('/discipline/infractions/{action}/resolve', [DisciplinaryActionController::class, 'resolve'])
        ->name('discipline.infractions.resolve');

    Route::delete('/discipline/infractions/{action}', [DisciplinaryActionController::class, 'destroy'])
        ->name('discipline.infractions.destroy');

    Route::get('/discipline/infractions/{action}/pdf', [DisciplinaryActionController::class, 'pdf'])
        ->name('discipline.infractions.pdf');
});
