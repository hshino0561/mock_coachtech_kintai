<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakLog;
use App\Models\StampCorrectionRequest;
use App\Models\BreakCorrectionRequest;

class AttendanceController extends Controller
{
    // 勤怠登録画面の表示
    public function show()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $now = now();
        $today = $now->toDateString();
        
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();
        
        // 条件①: 今日の勤怠レコードがない
        // 条件②: ステータスが after_work のまま
        if (!$attendance && $user->work_status === 'after_work') {
            $user->work_status = 'before_work';
            $user->save();
        }

        $work_status = $user->work_status ?? 'before_work';

        return view('pg03_attendance', compact('work_status'));
    }

    // 出勤
    public function start(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $today = today();

        // 「date が今日」かつ「start_time が null ではない」出勤があるか
        $count = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->whereNotNull('start_time')
            ->count();

        // デバッグコード追加
        // dd(Attendance::where('user_id', $user->id)
        //     ->where('date', $today)
        //     ->whereNotNull('start_time')
        //     ->count()
        //     ->get());

        if ($count >= 1) {
            return back()->withErrors(['msg' => '本日はすでに出勤済みです。']);
        }

        // 出勤登録処理
        Attendance::create([
            'user_id'    => $user->id,
            'date'       => $today,
            'start_time' => now(),
        ]);

        // ステータス更新
        $user->work_status = 'on_work';
        $user->save();

        return redirect()->route('attendance.show');
    }

    /**
     * 休憩開始処理
     * @return \Illuminate\Http\RedirectResponse
     */
    public function breakStart()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
    
        // 当日の出勤レコードを取得（※必ず "start_time" が登録されていることが前提）
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', today()) // ← date カラムを使用（適切なカラムに変更）
            ->whereNotNull('start_time')
            ->first();
    
        if (! $attendance) {
            return back()->withErrors(['msg' => '出勤記録が見つかりません。']);
        }
    
        // 勤務ステータス更新
        $user->work_status = 'on_break';
        $user->save();
    
        // 休憩ログを追加（※ attendance_id を明示）
        BreakLog::create([
            'user_id'       => $user->id,
            'attendance_id' => $attendance->id,
            'break_date'    => today(),
            'break_start'   => now(),
        ]);
    
        return redirect()->route('attendance.show');
    }

    /**
     * 休憩終了処理
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function breakEnd()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 当日の勤怠レコードを取得
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', today()) // ← カラムは "date" 想定
            ->first();

        if (! $attendance) {
            return back()->withErrors(['msg' => '出勤記録が見つかりません。']);
        }

        // 勤務ステータスを更新
        $user->work_status = 'on_work';
        $user->save();

        // 未終了の休憩レコードを取得して終了時間を更新
        $ongoingBreak = BreakLog::where('user_id', $user->id)
            ->where('attendance_id', $attendance->id)
            ->whereNull('break_end')
            ->orderByDesc('break_start') // 明示的に break_start で降順
            ->first();

        if (! $ongoingBreak) {
            return back()->withErrors(['msg' => '未終了の休憩記録が見つかりません。']);
        }

        $ongoingBreak->update([
            'break_end' => now(),
        ]);

        return redirect()->route('attendance.show');
    }

    // 退勤
    public function end()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $today = now()->toDateString();

        // 今日の勤怠レコードを取得
        $attendance = Attendance::where('user_id', $user->id)->whereNull('end_time')->latest('start_time')->first();

        // 「退勤」ボタンは1日1回のみ押下可能
        if (!$attendance || $attendance->end_time !== null) {
            return back()->withErrors(['msg' => '本日は既に退勤済みです。']);
        }

        // 押下時に「お疲れ様でした。」というメッセージを表示
        $attendance->end_time = now();
        $attendance->save();

        // ステータスが「退勤済（after_work）」に変更
        $user->work_status = 'after_work';
        $user->save();

        return redirect()->route('attendance.show')->with('message', 'お疲れ様でした。');
    }

    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
    
        $month = $request->query('month');
        $currentDate = $month ? Carbon::parse($month) : Carbon::now();
    
        // 勤怠データ取得（指定月）
        $attendances = Attendance::where('user_id', $user->id)
            ->whereMonth('date', $currentDate->month)
            ->whereYear('date', $currentDate->year)
            ->orderBy('date', 'asc')
            ->get();
    
        foreach ($attendances as $attendance) {
            // --- 休憩時間の集計（user_id + date） ---
            $logs = BreakLog::where('attendance_id', $attendance->id)
            ->whereNotNull('break_start')
            ->whereNotNull('break_end')
            ->get();
    
            $breakSeconds = $logs->reduce(function ($carry, $log) {
                $start = Carbon::parse($log->break_start)->setSecond(0);
                $end = Carbon::parse($log->break_end)->setSecond(0);            
                return $carry + $end->diffInSeconds($start);
            }, 0);
    
            $attendance->break_duration = sprintf('%02d:%02d', floor($breakSeconds / 3600), floor(($breakSeconds % 3600) / 60));
    
            // --- 合計勤務時間（出勤・退勤が両方ある場合） ---
            if ($attendance->start_time && $attendance->end_time) {
                $startTime = Carbon::parse($attendance->start_time)->setSecond(0);
                $endTime = Carbon::parse($attendance->end_time)->setSecond(0);
            
                $workDurationSeconds = $endTime->diffInSeconds($startTime) - $breakSeconds;
                $workDurationSeconds = max(0, $workDurationSeconds); // マイナス対策
            
                $workHours = floor($workDurationSeconds / 3600);
                $workMinutes = floor(($workDurationSeconds % 3600) / 60);
            
                $attendance->work_duration = sprintf('%02d:%02d', $workHours, $workMinutes);
            } else {
                $attendance->work_duration = '-';
            }
        }
    
        return view('pg04_attendance_list', compact('attendances', 'currentDate'));
    }

    public function showDetail($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
    
        // 勤怠レコードの取得（ログインユーザー自身のみ）
        $attendance = Attendance::with(['user', 'breakLogs'])
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();
    
        // breakLogs は attendance モデルに既に eager load 済み
        $breaks = $attendance->breakLogs;
    
        return view('pg05_attendance_detail', [
            'user'         => $user,
            'attendance'   => $attendance,
            'breaks'       => $breaks,
            'readonly'     => false,
            'from_request' => false,
        ]);
    }
}