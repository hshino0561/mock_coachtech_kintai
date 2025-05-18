<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Attendance\AttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;

// 会員登録画面(一般ユーザ)（PG01）
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// ログイン画面(一般ユーザ)（PG02）
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

// ログアウト
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

// 勤怠登録関連画面（ログイン済ユーザー専用）（PG03）
Route::middleware('auth')->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'show'])->name('attendance.show');
    // 勤務開始・終了
    Route::post('/attendance/start', [AttendanceController::class, 'start'])->name('attendance.start');
    Route::post('/attendance/end', [AttendanceController::class, 'end'])->name('attendance.end');

    // 休憩開始・終了（明示的にルート分離）
    Route::post('/attendance/break/start', [AttendanceController::class, 'breakStart'])->name('attendance.break.start');
    Route::post('/attendance/break/end', [AttendanceController::class, 'breakEnd'])->name('attendance.break.end');

    // 勤怠一覧・今月の出勤一覧画面（PG04）
    Route::get('/attendance/list', [AttendanceController::class, 'index'])->name('attendance.list');

    // 勤怠詳細画面（PG05）
    Route::get('/attendance/{id}', [AttendanceController::class, 'showDetail'])->name('attendance.detail');

    // 勤怠詳細画面（PG05）：修正機能
    Route::put('/attendance/{id}', [AttendanceController::class, 'update'])->name('attendance.update');

    // 勤怠詳細画面（PG05）：修正申請機能
    Route::post('/stamp_correction_request/{attendance}', [StampCorrectionRequestController::class, 'store'])
    ->name('stamp_correction_request.store');

    // 申請一覧画面（PG06）
    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'index'])
        ->name('stamp_correction_request.list');

    Route::get('/stamp_correction_request/{id}', [StampCorrectionRequestController::class, 'detail'])
    ->name('stamp_correction_request.detail');
});

// 管理者
Route::prefix('admin')->middleware(['auth:admin'])->group(function () {
    Route::get('/attendance/list', [\App\Http\Controllers\Admin\AttendanceController::class, 'list'])->name('admin.attendance.list');
});
