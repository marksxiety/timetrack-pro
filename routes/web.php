<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OvertimeRequestController;
use App\Http\Controllers\ShiftContoller;
use App\Http\Controllers\RequiredHoursController;
use App\Http\Controllers\ScheduleController;
use App\Models\OvertimeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\OpenAIController;
use App\Http\Controllers\SettingsController;
use App\Models\Setting;

Route::middleware(['guest'])->group(function () {
    Route::get('/register', [AuthController::class, 'directRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::inertia('/login', 'Auth/Login')->name('login');
    Route::inertia('/forgot-password', 'Auth/ForgotPassword')->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::get('/', function (Request $request) {
    $role = Auth::user()->role;
    return match ($role) {
        'admin', 'approver' => app(OvertimeRequestController::class)->fetchTotalOvertimeRequests($request),
        'employee' => app(OvertimeRequestController::class)->fetchOvertimeRequestsBySession($request),
        default => redirect()->route('unauthorized'),
    };
})->middleware('auth')->name('main');

Route::middleware('employee')->group(function () {
    // shift code list for registering schedule requests (axios)
    Route::get('/employee/shift/list', [ShiftContoller::class, 'shiftCodeList']);

    // schedule routes
    Route::get('/schedule', [ScheduleController::class, 'schedulePage'])->name('schedule');
    Route::get('/schedule/list', [ScheduleController::class, 'fetchSchedule']);
    Route::get('/schedule/user', [ScheduleController::class, 'getUserSchedule']);
    Route::post('/schedule/submit', [ScheduleController::class, 'submitSchedule'])->name('schedule.submit');

    // overtime request routes
    Route::get('/overtime/file', [OvertimeRequestController::class, 'overtimeFilingPage'])->name('overtime.file');
    Route::post('/overtime/request', [OvertimeRequestController::class, 'insertOvertimeRequest'])->name('overtime.request');
    Route::post('/overtime/request/bulk', [OvertimeRequestController::class, 'insertBulkOvertimeRequest'])->name('overtime.request.bulk');
    Route::post('/overtime/update/employee', [OvertimeRequestController::class, 'updateOvertimeRequestStatus'])->name('overtime.update.employee');

    Route::get('/employee/profile', [AuthController::class, 'loadUserProfile'])->name('profile.employee');

    Route::post('/profile/update/employee', [AuthController::class, 'updateProfileInformation'])->name('profile.update.employee');

    Route::get('/overtime/requests', [OvertimeRequestController::class, 'fetchOvertimeRequestOfEmployee'])->name('overtime.requests.employee');
    Route::get('/overtime/heatmap', [OvertimeRequestController::class, 'fetchOvertimeHeatmap']);
});

Route::middleware('admin-approver')->group(function () {

    Route::get('/shift', [ShiftContoller::class, 'registeredShiftCodes'])->name('shifts');
    Route::post('/shift/register', [ShiftContoller::class, 'insertShiftCode'])->name('shift.register'); // insertion route
    Route::put('/shift/{shift}', [ShiftContoller::class, 'updateShiftCode'])->name('shift.update'); // update route
    Route::delete('/shift/{shift}', [ShiftContoller::class, 'deleteShiftCode'])->name('shift.delete'); // delete route

    // required hours routes
    Route::get('/hours', [RequiredHoursController::class, 'registeredRequiredHours'])->name('hours');
    Route::post('/hours/register', [RequiredHoursController::class, 'registerRequiredHours'])->name('hours.register');
    Route::put('/hours/{requiredHours}', [RequiredHoursController::class, 'updateRequiredHour'])->name('hours.update');

    Route::post('/overtime/update/approver', [OvertimeRequestController::class, 'updateOvertimeRequestStatus'])->name('overtime.update.approver');
    Route::post('/overtime/update/bulk', [OvertimeRequestController::class, 'bulkUpdateOvertimeRequestStatus'])->name('overtime.update.bulk');
    Route::get('/overtime/filing', [OvertimeRequestController::class, 'fetchOvertimeRequestsViaStatus'])->name('overtime.filing');
    Route::get('/overtime/pending', [OvertimeRequestController::class, 'fetchOvertimeRequestsViaStatus'])->name('overtime.pending');
    Route::get('/overtime/filed', [OvertimeRequestController::class, 'fetchOvertimeRequestsViaStatus'])->name('overtime.filed');

    Route::inertia('/schedule/manage', 'Approver/ManageSchedule')->name('schedule.manage');
    Route::get('/schedule/employee/list', action: [ScheduleController::class, 'fetchEmployeeSchedule']);

    Route::get('/approver/shift/list', [ShiftContoller::class, 'shiftCodeList']);
    Route::post('/schedule/employee/submit', [ScheduleController::class, 'submitEmployeeSchedules']);

    Route::get('/users/registered', [AuthController::class, 'RegisteredUsers'])->name('approver.manage.user');
    Route::post('/users/update', [AuthController::class, 'updateUserInformation'])->name('approver.update.user');

    Route::inertia('/generate/report/option', 'Approver/Report')->name('approver.generate.report');
    Route::get('/generate/report', [OvertimeRequestController::class, 'fetchOvertimeRequestsViaDateRange'])->name('approver.generate.report.daterange');
});

Route::get('/404', fn() => Inertia::render('Unauthorized'))->name('404');

Route::middleware('auth')->group(function () {
    Route::post('/ai/analyze', [OpenAIController::class, 'analyze'])->name('ai.analyze');
    Route::post('/ai/enhance', [OpenAIController::class, 'enhance'])->name('ai.enhance');
});

Route::middleware('auth')->post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('admin')->group(function () {
    Route::get('/admin/settings', [SettingsController::class, 'index'])->name('admin.settings');
    Route::put('/admin/settings', [SettingsController::class, 'update'])->name('admin.settings.update');
});

Route::get('/setup/config', function () {
    $settings = Setting::all()->pluck('value', 'key')->map(function ($value) {
        $decoded = json_decode($value, true);
        return $decoded !== null ? $decoded : $value;
    })->toArray();

    $settings['ai_model'] = env('AI_MODEL', 'gpt-4o-mini');
    return response()->json($settings);
});
