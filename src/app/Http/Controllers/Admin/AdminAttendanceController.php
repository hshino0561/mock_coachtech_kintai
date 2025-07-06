<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;
use App\Models\Attendance;
use App\Models\BreakLog;
use App\Models\StampCorrectionRequest;
use App\Models\BreakCorrectionRequest;
use Carbon\Carbon;
use App\Http\Requests\Admin\AdminStoreStampCorrectionRequest;

class AdminAttendanceController extends Controller
{
     // PG08：勤怠一覧（日付指定・休憩・勤務時間集計）
    public function index(Request $request)
    {
        $dateParam    = $request->query('date');
        $currentDate  = $dateParam ? Carbon::parse($dateParam) : Carbon::today();

        // 勤怠データ取得（該当日）
        $attendances = Attendance::with('user')
            ->whereDate('date', $currentDate)
            ->orderBy('start_time')
            ->get();

        foreach ($attendances as $attendance) {
            // --- 休憩時間の集計（attendance_idベース） ---
            $logs = BreakLog::where('attendance_id', $attendance->id)
                ->whereNotNull('break_start')
                ->whereNotNull('break_end')
                ->get();

            $breakMinutes = $logs->reduce(function ($carry, $log) {
                // 時刻の秒を丸めた状態で比較（CarbonのdiffInMinutesで切り捨て確定）
                $start = Carbon::parse($log->break_start)->copy()->seconds(0);
                $end = Carbon::parse($log->break_end)->copy()->seconds(0);
                return $carry + $end->diffInMinutes($start); // 分単位
            }, 0);

            $attendance->break_duration_formatted = sprintf('%d:%02d',
                floor($breakMinutes / 60),
                $breakMinutes % 60
            );

            // --- 合計勤務時間（出退勤がある場合） ---
            if ($attendance->start_time && $attendance->end_time) {
                $start = Carbon::parse($attendance->start_time)->copy()->seconds(0);
                $end = Carbon::parse($attendance->end_time)->copy()->seconds(0);

                $workMinutes = $end->diffInMinutes($start) - $breakMinutes;
                $workMinutes = max($workMinutes, 0); // マイナス防止

                $attendance->work_duration_formatted = sprintf('%d:%02d',
                    floor($workMinutes / 60),
                    $workMinutes % 60
                );
            } else {
                $attendance->work_duration_formatted = '-';
            }
        }

        return view('admin.pg08_admin_attendance_list', [
            'attendances' => $attendances,
            'currentDate' => $currentDate,
        ]);
    }

    /**
     * 勤怠詳細 or 修正申請承認画面を一元表示（PG09／PG13）
     * @param int $id
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function showDetail($id)
    {
        /** @var \App\Models\Admin|null $admin */
        $admin = Auth::guard('admin')->user();
        if (! $admin) {
            abort(403);
        }
    
        $routeName = Route::currentRouteName();
    
        if ($routeName === 'admin.stamp_correction_request.detail') {
            // 承認画面（PG13）: $id は stamp_correction_requests.id
            $stampRequest = StampCorrectionRequest::with(['attendance.user'])->findOrFail($id);
            $attendance = $stampRequest->attendance;
    
            // 修正申請用の休憩時間を取得（break_correction_requests）
            $breaks = BreakCorrectionRequest::where('stamp_correction_request_id', $stampRequest->id)->get();
    
            // 空の休憩追加（readonly表示対応用、Bladeで必要な場合）
            $breaks->push((object)[
                'break_start' => null,
                'break_end'   => null,
            ]);

            $date = $attendance->date ?? ($stampRequest->attendance->date ?? null);

            return view('admin.pg13_admin_stamp_correction_approve', [
                'user'         => $attendance->user,
                'attendance'   => $stampRequest, // 勤怠は申請データベース
                'stampRequest' => $stampRequest,
                'date'         => $date,
                'breaks'       => $breaks,
                'from_request' => true,
            ]);
        }
    
        // 勤怠詳細画面（PG09）: $id は attendances.id
        $attendance = Attendance::with(['breakLogs', 'user', 'stampCorrectionRequests'])->findOrFail($id);
    
        return view('admin.pg09_admin_attendance_detail', [
            'attendance'    => $attendance,
            'breaks'        => $attendance->breakLogs,
            'user'          => $attendance->user,
            'from_request'  => false,
        ]);
    }

    public function update(AdminStoreStampCorrectionRequest $request, $id)
    {
        Log::debug('★承認updateメソッド呼び出し');
    
        $startTime = $request->input('start_time');
        $endTime   = $request->input('end_time');
        $breaks    = $request->input('breaks', []);
        $memo      = $request->input('memo');
    
        Log::debug('★受け取ったstart_time: ' . $startTime);
        Log::debug('★受け取ったend_time: ' . $endTime);
        Log::debug('★受け取ったbreaks: ', $breaks);
        Log::debug('★受け取ったmemo: ' . $memo);
    
        /** @var \App\Models\Admin|null $admin */
        $admin = Auth::guard('admin')->user();
        if (! $admin) {
            abort(403);
        }
    
        $routeName  = Route::currentRouteName();
        $isApprove  = $request->input('action') === 'approve';
    
        if ($routeName === 'admin.stamp_correction_request.approve') {
            // 修正申請経由（PG13）
            $stampRequest = StampCorrectionRequest::with('attendance')->findOrFail($id);
            $attendance   = $stampRequest->attendance;
        } else {
            // 通常の勤怠詳細からの更新（PG09）
            $attendance   = Attendance::findOrFail($id);
            $stampRequest = null;
        }
    
        // 勤怠本体の更新
        $attendance->start_time = $startTime;
        $attendance->end_time   = $endTime;
        $attendance->memo       = $memo;
        $attendance->save();
    
        // --- break_logs 更新処理 ---
        Log::debug('=== break_logs更新処理開始 ===');
        $attendanceDate = Carbon::parse($attendance->date)->format('Y-m-d');
    
        // 既存break_logsをインデックス順で取得（主に更新対象用）
        $existingBreaks = $attendance->breakLogs->values();
    
        foreach ($breaks as $index => $breakInput) {
            $rawStart = $breakInput['start'] ?? null;
            $rawEnd   = $breakInput['end'] ?? null;
    
            if (empty($rawStart) && empty($rawEnd)) {
                Log::debug("スキップ: 空の休憩（index: {$index}）");
                continue;
            }
    
            $start = Carbon::parse("{$attendanceDate} {$rawStart}");
            $end   = Carbon::parse("{$attendanceDate} {$rawEnd}");
    
            if (isset($existingBreaks[$index])) {
                // 既存のレコードを更新
                $updated = $existingBreaks[$index]->update([
                    'break_start' => $start,
                    'break_end'   => $end,
                ]);
                Log::debug("更新済 break_id={$existingBreaks[$index]->id} : start={$start}, end={$end}, 成功: {$updated}");
            } else {
                // 新規レコードを追加
                $new = BreakLog::create([
                    'attendance_id' => $attendance->id,
                    'user_id'       => $attendance->user_id,
                    'break_date'    => $attendance->date,
                    'break_start'   => $start,
                    'break_end'     => $end,
                ]);
                Log::debug("新規作成 break_id={$new->id} : start={$start}, end={$end}");
            }
        }
        Log::debug('=== break_logs更新処理終了 ===');
    
        // 申請の承認処理（承認ルートのみ）
        if ($isApprove && $stampRequest) {
            $stampRequest->status = 'approved';
            $stampRequest->save();
        }
    
        return redirect()
            ->route($isApprove ? 'stamp_correction_request.list' : 'admin.attendance.list')
            ->with('status', $isApprove ? '申請を承認しました。' : '勤怠情報を更新しました。');
    }
}
