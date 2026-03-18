<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\CalendarEventController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClockController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\EmployeeProfileController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\WorkingHourController;

Route::middleware(['web', 'auth'])->group(function () {
    // Employee routes - exclude admin users
    Route::middleware(['redirect.admin'])->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard.page');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/supervisor', [DashboardController::class, 'showSupervisor'])->name('supervisor.show');
        Route::get('/user/{id}', [UserController::class, 'show'])->name('user.show');

        // Report routes
        Route::get('/reports/{document}/download', [\App\Http\Controllers\ReportController::class, 'download'])->name('reports.download');
        Route::get('/reports/{document}/download-signed', [\App\Http\Controllers\ReportController::class, 'downloadSigned'])->name('reports.download-signed');
        Route::get('/reports/{document}/preview', [\App\Http\Controllers\ReportController::class, 'preview'])->name('reports.preview');
        Route::post('/reports/{document}/submit', [\App\Http\Controllers\ReportController::class, 'submit'])->name('reports.submit');
        Route::get('/reports/{document}/sign-form', [\App\Http\Controllers\ReportController::class, 'showSignForm'])->name('reports.sign.form');
        Route::post('/reports/{document}/sign', [\App\Http\Controllers\ReportController::class, 'sign'])->name('reports.sign');
        Route::resource('reports', \App\Http\Controllers\ReportController::class)->parameters(['reports' => 'document']);

        // Clock routes
        Route::get('/clock', [ClockController::class, 'index'])->name('clock.index');
        Route::post('/clock/in', [ClockController::class, 'clockIn'])->name('clock.clock-in');
        Route::post('/clock/out', [ClockController::class, 'clockOut'])->name('clock.clock-out');
        Route::post('/clock/break-in', [ClockController::class, 'breakIn'])->name('clock.break-in');
        Route::post('/clock/break-out', [ClockController::class, 'breakOut'])->name('clock.break-out');

        // Attendance routes
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('/attendance/today', [AttendanceController::class, 'getTodayAttendance'])->name('attendance.today');
        Route::post('/attendance/{id}/edit', [AttendanceController::class, 'edit'])->name('attendance.edit');

    });

    // Leave routes - accessible by all authenticated users (moved outside redirect.admin)
    Route::resource('leave', \App\Http\Controllers\LeaveController::class);

    // Claims - accessible by all authenticated users
    Route::get('/claims', [ClaimController::class, 'index'])->name('claims.index');
    Route::get('/claims/create', [ClaimController::class, 'create'])->name('claims.create');
    Route::post('/claims', [ClaimController::class, 'store'])->name('claims.store');
    Route::get('/claims/{claim}', [ClaimController::class, 'show'])->name('claims.show');
    Route::patch('/claims/{claim}/submit', [ClaimController::class, 'submit'])->name('claims.submit');
    Route::post('/claims/{claim}/item', [ClaimController::class, 'addItem'])->name('claims.item.add');
    Route::delete('/claims/item/{item}', [ClaimController::class, 'removeItem'])->name('claims.item.remove');
    Route::patch('/claims/{claim}/approve', [ClaimController::class, 'approve'])->name('claims.approve');
    Route::patch('/claims/{claim}/reject', [ClaimController::class, 'reject'])->name('claims.reject');
    Route::patch('/claims/{claim}/mark-paid', [ClaimController::class, 'markPaid'])->name('claims.mark-paid');

    // Messages - accessible by all authenticated users
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/create', [MessageController::class, 'create'])->name('messages.create');
    Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');
    Route::get('/messages/{message}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{message}/reply', [MessageController::class, 'reply'])->name('messages.reply');

    // Announcements - accessible by all authenticated users
    Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');

    // Holiday Calendar routes - accessible by all authenticated users
    Route::get('/holidays', [HolidayController::class, 'index'])->name('holidays.index');
    Route::get('/holidays/calendar-data', [HolidayController::class, 'calendarData'])->name('holidays.calendar-data');

    // Calendar Events routes - accessible by all authenticated users
    Route::get('/calendar', [CalendarEventController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/events-data', [CalendarEventController::class, 'eventsData'])->name('calendar.events-data');
    Route::post('/calendar', [CalendarEventController::class, 'store'])->name('calendar.store');
    Route::put('/calendar/{event}', [CalendarEventController::class, 'update'])->name('calendar.update');
    Route::delete('/calendar/{event}', [CalendarEventController::class, 'destroy'])->name('calendar.destroy');

    // Employee Profile routes - accessible by all authenticated users
    Route::get('/my-profile', [EmployeeProfileController::class, 'show'])->name('employee-profile.show');
    Route::get('/my-profile/edit', [EmployeeProfileController::class, 'edit'])->name('employee-profile.edit');
    Route::put('/my-profile', [EmployeeProfileController::class, 'update'])->name('employee-profile.update');
    Route::get('/my-profile/{id}', [EmployeeProfileController::class, 'show'])->name('employee-profile.show.admin');
    Route::get('/my-profile/{id}/edit', [EmployeeProfileController::class, 'edit'])->name('employee-profile.edit.admin');
    Route::put('/my-profile/{id}', [EmployeeProfileController::class, 'update'])->name('employee-profile.update.admin');
    Route::post('/employee/{userId}/documents', [EmployeeProfileController::class, 'storeDocument'])->name('employee-profile.document.store');
    Route::get('/employee-document/{document}/download', [EmployeeProfileController::class, 'downloadDocument'])->name('employee-profile.document.download');
    Route::delete('/employee-document/{document}', [EmployeeProfileController::class, 'deleteDocument'])->name('employee-profile.document.delete');
    Route::post('/employee/{userId}/history', [EmployeeProfileController::class, 'storeHistory'])->name('employee-profile.history.store');

    // Profile routes - accessible by all authenticated users
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Payroll routes - accessible by all authenticated users
    Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');
    Route::get('/payroll/{payroll}', [PayrollController::class, 'show'])->name('payroll.show');
    Route::get('/payroll/{payroll}/payslip', [PayrollController::class, 'payslip'])->name('payroll.payslip');
    Route::post('/payroll/generate', [PayrollController::class, 'generate'])->name('payroll.generate');
    Route::post('/payroll/{payroll}/item', [PayrollController::class, 'addItem'])->name('payroll.item.add');
    Route::delete('/payroll/item/{item}', [PayrollController::class, 'removeItem'])->name('payroll.item.remove');
    Route::patch('/payroll/{payroll}/approve', [PayrollController::class, 'approve'])->name('payroll.approve');
    Route::patch('/payroll/{payroll}/mark-paid', [PayrollController::class, 'markPaid'])->name('payroll.mark-paid');

    // Leave approval routes - accessible by supervisors and admins
    Route::middleware(['check.supervisor.admin'])->group(function () {
        Route::get('/leave-approvals', [\App\Http\Controllers\LeaveController::class, 'pendingApprovals'])->name('leave.approvals');
        Route::get('/leave/{id}/approve', function ($id) {
            return redirect()->route('leave.show', $id)->with('error', 'Please use the form to approve the leave.');
        })->name('leave.approve.get');
        Route::patch('/leave/{id}/approve', [\App\Http\Controllers\LeaveController::class, 'approve'])->name('leave.approve');
        Route::patch('/leave/{id}/reject', [\App\Http\Controllers\LeaveController::class, 'reject'])->name('leave.reject');
    });
});

// Admin routes
Route::middleware(['web', 'auth'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    // Users
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::get('/users/create', [AdminController::class, 'createUser'])->name('admin.users.create');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('admin.users.store');
    Route::get('/users/{id}/edit', [AdminController::class, 'editUser'])->name('admin.users.edit');
    Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('admin.users.delete');

    // Departments
    Route::get('/departments', [AdminController::class, 'departments'])->name('admin.departments');
    Route::post('/departments', [AdminController::class, 'storeDepartment'])->name('admin.departments.store');
    Route::put('/departments/{id}', [AdminController::class, 'updateDepartment'])->name('admin.departments.update');
    Route::delete('/departments/{id}', [AdminController::class, 'deleteDepartment'])->name('admin.departments.delete');

    // Other admin routes
    Route::get('/attendances', [AdminController::class, 'attendances'])->name('admin.attendances');
    Route::get('/leaves', [AdminController::class, 'leaves'])->name('admin.leaves');
    Route::get('/audit-logs', [AdminController::class, 'auditLogs'])->name('admin.audit-logs');
    Route::get('/reports', [AdminController::class, 'reports'])->name('admin.reports');

    // Office Locations (Geofencing)
    Route::get('/office-locations', [AdminController::class, 'officeLocations'])->name('admin.office-locations');
    Route::post('/office-locations', [AdminController::class, 'storeOfficeLocation'])->name('admin.office-locations.store');
    Route::put('/office-locations/{id}', [AdminController::class, 'updateOfficeLocation'])->name('admin.office-locations.update');
    Route::delete('/office-locations/{id}', [AdminController::class, 'deleteOfficeLocation'])->name('admin.office-locations.delete');

    // Holiday management (admin)
    Route::post('/holidays', [HolidayController::class, 'store'])->name('holidays.store');
    Route::put('/holidays/{holiday}', [HolidayController::class, 'update'])->name('holidays.update');
    Route::delete('/holidays/{holiday}', [HolidayController::class, 'destroy'])->name('holidays.destroy');

    // Working Hours Configuration
    Route::get('/working-hours', [WorkingHourController::class, 'index'])->name('admin.working-hours');
    Route::put('/working-hours/default', [WorkingHourController::class, 'updateDefault'])->name('admin.working-hours.update-default');
    Route::post('/working-hours/custom', [WorkingHourController::class, 'storeCustom'])->name('admin.working-hours.store-custom');
    Route::delete('/working-hours/{id}', [WorkingHourController::class, 'deleteCustom'])->name('admin.working-hours.delete-custom');

    // Announcements management (admin)
    Route::post('/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
    Route::put('/announcements/{announcement}', [AnnouncementController::class, 'update'])->name('announcements.update');
    Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');

    // Department HOD assignment
    Route::put('/departments/{department}/hod', [AdminController::class, 'assignHod'])->name('admin.departments.assign-hod');

    // Report generation routes
    Route::post('/reports/generate/attendance', [AdminController::class, 'generateAttendanceReport'])->name('admin.reports.generate.attendance');
    Route::post('/reports/generate/leave', [AdminController::class, 'generateLeaveReport'])->name('admin.reports.generate.leave');
    Route::post('/reports/generate/employee', [AdminController::class, 'generateEmployeeReport'])->name('admin.reports.generate.employee');
    Route::post('/reports/generate/department', [AdminController::class, 'generateDepartmentReport'])->name('admin.reports.generate.department');
    Route::post('/reports/generate/monthly-summary', [AdminController::class, 'generateMonthlySummaryReport'])->name('admin.reports.generate.monthly-summary');
    Route::post('/reports/generate/audit', [AdminController::class, 'generateAuditReport'])->name('admin.reports.generate.audit');
});

require __DIR__.'/auth.php';
