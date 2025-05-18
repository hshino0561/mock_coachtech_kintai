<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;

class AttendanceController extends Controller
{
    // 勤怠登録画面の表示
    public function show()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $today = now()->toDateString();
    
        // 今日の出勤レコードを取得
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('start_time', $today)
            ->first();
    
        // 条件①: 今日の勤怠レコードがない
        // 条件②: ステータスが after_work のまま（前日の退勤済）
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
        $today = now()->toDateString();

        // 出勤回数のチェック（仮：100回まで許容）
        $count = Attendance::where('user_id', $user->id)->whereDate('start_time', $today)->count();

        if ($count >= 100) {
            return back()->withErrors(['msg' => '本日の出勤上限に達しています。']);
        }

        // 出勤記録を登録
        Attendance::create([
            'user_id' => $user->id,
            'start_time' => now(),
        ]);

        // ステータスを「出勤中」に更新
        $user->work_status = 'on_work';
        $user->save();

        return redirect()->route('attendance.show');
    }

    // 休憩開始
    public function breakStart()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 新しい休憩開始を登録
        $user->work_status = 'on_break';
        $user->save();

        $user->breakLogs()->create([
            'break_start' => now(),
        ]);

        return redirect()->route('attendance.show');
    }

    // 休憩終了
    public function breakEnd()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // ステータス更新
        $user->work_status = 'on_work';
        $user->save();

        // 最後の未終了の休憩を更新
        $user
            ->breakLogs()
            ->whereNull('break_end')
            ->latest()
            ->first()
            ?->update(['break_end' => now()]);

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

        // クエリパラメータから月を取得（なければ今月）
        $month = $request->query('month');
        $currentDate = $month ? Carbon::parse($month) : Carbon::now();

        // 当該月の範囲で絞り込む
        $attendances = Attendance::where('user_id', $user->id)
            ->whereMonth('date', $currentDate->month)
            ->whereYear('date', $currentDate->year)
            ->orderBy('date', 'asc')
            ->get();

        return view('pg04_attendance_list', compact('attendances', 'currentDate'));
    }

    public function showDetail($id)
    {
        // 指定IDの勤怠詳細画面を表示
        /** @var \App\Models\User $user */
        $user = Auth::user();
    
        $attendance = Attendance::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();
    
            return view('pg05_attendance_detail', [
                'attendance' => $attendance,
                'user' => $user,
            ]);
    }    

    public function update(Request $request, $id)
    {
        // $request->validate([
        //     'start_time' => 'nullable|date_format:H:i',
        //     'end_time' => 'nullable|date_format:H:i',
        //     'break_start' => 'nullable|date_format:H:i',
        //     'break_end' => 'nullable|date_format:H:i',
        //     'note' => 'nullable|string|max:255',
        // ]);
    
        $attendance = Attendance::findOrFail($id);
    
        $attendance->start_time = $request->start_time;
        $attendance->end_time = $request->end_time;
        $attendance->break_start = $request->break_start;
        $attendance->break_end = $request->break_end;
        $attendance->note = $request->note;
        $attendance->save();
    
        return redirect()->route('attendance.show', ['id' => $id])->with('success', '勤怠情報を更新しました。');
    }    
}