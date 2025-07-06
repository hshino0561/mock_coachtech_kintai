<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Attendance\AttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Laravel\Fortify\Http\Controllers\RegisteredUserController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Admin\AdminStaffController;

// ------------------------------------
// 公開ルート（ゲスト専用）
// ------------------------------------
Route::middleware(['guest'])->group(function () {
    // 会員登録画面（PG01）
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

    // ログイン画面（PG02）
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    // 管理者ログイン画面表示（PG07）
    Route::get('/admin/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');

    // 管理者ログイン処理
    Route::post('/admin/login', [AdminLoginController::class, 'login']);
});

// ------------------------------------
// メール認証関連ルート（Fortify互換）
// ------------------------------------

// 認証待ち画面（email_verified_at が null の場合に誘導される）
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

// メール内リンクをクリックしたときの処理
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill(); // email_verified_at に現在時刻が入る
    return redirect()->route('attendance.show'); // 認証後の遷移先
})->middleware(['auth', 'signed'])->name('verification.verify');

// 認証メールの再送信処理（ボタン押下時）
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('status', 'verification-link-sent');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send'); // 1分あたり最大6回に制限（スパム送信防止）

// ------------------------------------
// ログイン済ユーザー専用ルート
// ------------------------------------
Route::middleware(['web', 'auth', 'verified'])->group(function () {
    // 勤怠登録画面（PG03）
    Route::get('/attendance', [AttendanceController::class, 'show'])->name('attendance.show');
    // 勤務開始・終了
    Route::post('/attendance/start', [AttendanceController::class, 'start'])->name('attendance.start');
    Route::post('/attendance/end', [AttendanceController::class, 'end'])->name('attendance.end');

    // 休憩開始・終了（明示的にルート分離）
    Route::post('/attendance/break/start', [AttendanceController::class, 'breakStart'])->name('attendance.break.start');
    Route::post('/attendance/break/end', [AttendanceController::class, 'breakEnd'])->name('attendance.break.end');

    // 勤怠一覧画面（PG04）：今月の出勤一覧画面
    Route::get('/attendance/list', [AttendanceController::class, 'index'])->name('attendance.list');

    // 勤怠詳細画面（PG05）：勤怠一覧画面内の詳細メニューから呼び出し用
    Route::get('/attendance/{id}', [AttendanceController::class, 'showDetail'])->name('attendance.detail');

    // 勤怠詳細画面（PG05）：修正申請機能
    Route::post('/stamp_correction_request/{attendance}', [StampCorrectionRequestController::class, 'store'])
    ->name('stamp_correction_request.store');

    // 勤怠詳細画面リンク用（PG06）：申請一覧画面内の詳細メニューから呼び出し用
    Route::get('/stamp_correction_request/request/{stamp_correction_request}', [StampCorrectionRequestController::class, 'showDetail'])
    ->name('stamp_correction_request.show_detail');

    // ログアウト（一般ユーザ用）
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

// 申請一覧画面（PG06）/ 申請一覧画面（PG12）
Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'index'])
    ->name('stamp_correction_request.list')
    ->middleware(['web']);

// 管理者用
Route::middleware(['web', 'auth:admin'])->group(function () {
    // 勤怠一覧画面（PG08）
    Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'index'])->name('admin.attendance.list');

    // 勤怠詳細画面（PG09）
    Route::get('/admin/attendance/{id}', [AdminAttendanceController::class, 'showDetail'])
        ->name('admin.attendance.detail');

    // 勤怠詳細画面（PG09）：修正処理
    Route::patch('/admin/attendance/{attendance}', [AdminAttendanceController::class, 'update'])
        ->name('admin.attendance.update');

    // スタッフ一覧（PG10）
    Route::get('/admin/staff/list', [AdminStaffController::class, 'index'])->name('admin.staff.list');

    // スタッフ別勤怠一覧（PG11）
    Route::get('/admin/attendance/staff/{id}', [AdminStaffController::class, 'showAttendance'])->name('admin.attendance.staff');

    // スタッフ別勤怠一覧（PG11）：CSV出力機能
    Route::get('/admin/attendance/staff/{id}/export', [AdminStaffController::class, 'exportCsv'])
        ->name('admin.attendance.staff.export');

    // 修正申請承認画面（PG13）：PG12の詳細リンクから遷移
    Route::get('/stamp_correction_request/approve/{attendance_correct_request}', [AdminAttendanceController::class, 'showDetail'])
        ->name('admin.stamp_correction_request.detail');

    // 修正申請承認画面（PG13）：承認ボタン処理
    Route::patch('/stamp_correction_request/approve/{attendance_correct_request}', [AdminAttendanceController::class, 'update'])
        ->name('admin.stamp_correction_request.approve');

    // ログアウト（管理者用）
    Route::post('/admin/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');
});
