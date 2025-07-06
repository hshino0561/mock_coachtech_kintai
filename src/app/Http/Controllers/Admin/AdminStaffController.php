<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;

use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakLog;

class AdminStaffController extends Controller
{
    /**
     * スタッフ一覧表示
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $staffs = User::orderBy('id')->get();

        return view('admin.pg10_admin_staff_list', compact('staffs'));
    }

    public function showAttendance($id, Request $request)
    {
        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('admin')->user();
    
        $month = $request->input('month', now()->format('Y-m'));
        $startOfMonth = Carbon::parse($month)->startOfMonth();
        $endOfMonth = Carbon::parse($month)->endOfMonth();
    
        $staff = User::findOrFail($id);
    
        $attendances = Attendance::where('user_id', $id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->with('breakLogs')
            ->orderBy('date')
            ->get();
    
        foreach ($attendances as $attendance) {
            // 休憩時間（分単位に丸めて合計）
            $logs = $attendance->breakLogs->filter(function ($log) {
                return $log->break_start && $log->break_end;
            });
    
            $breakMinutes = $logs->reduce(function ($carry, $log) {
                $start = Carbon::parse($log->break_start)->copy()->seconds(0);
                $end = Carbon::parse($log->break_end)->copy()->seconds(0);
                return $carry + $end->diffInMinutes($start);
            }, 0);
    
            $attendance->break_duration_formatted = sprintf('%d:%02d',
                floor($breakMinutes / 60),
                $breakMinutes % 60
            );
    
            // 労働時間（出退勤あり時のみ）
            if ($attendance->start_time && $attendance->end_time) {
                $start = Carbon::parse($attendance->start_time)->copy()->seconds(0);
                $end = Carbon::parse($attendance->end_time)->copy()->seconds(0);
                $workMinutes = $end->diffInMinutes($start) - $breakMinutes;
                $workMinutes = max(0, $workMinutes);
    
                $attendance->work_duration_formatted = sprintf('%d:%02d',
                    floor($workMinutes / 60),
                    $workMinutes % 60
                );
            } else {
                $attendance->work_duration_formatted = '-';
            }
        }
    
        return view('admin.pg11_admin_staff_attendance_list', compact('staff', 'attendances', 'month'));
    }    

    public function exportCsv($id, Request $request): StreamedResponse
    {
        $month = $request->input('month', now()->format('Y-m'));
        $startOfMonth = Carbon::parse($month)->startOfMonth();
        $endOfMonth = Carbon::parse($month)->endOfMonth();
    
        $staff = User::findOrFail($id);
    
        $attendances = Attendance::where('user_id', $id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->with('breakLogs')
            ->get();
    
        // 氏名をスペースなしに変換（例：'吉田 桃子' → '吉田桃子'）
        $name = str_replace([' ', '　'], '', $staff->name);

        // ファイル名に使用
        $filename = $name . '_勤怠一覧_' . $month . '.csv';
    
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
    
        $callback = function () use ($attendances) {
            $stream = fopen('php://output', 'w');
            // ヘッダー行
            fputcsv($stream, ['日付', '出勤', '退勤', '休憩', '合計']);
    
            foreach ($attendances as $attendance) {
                $date = Carbon::parse($attendance->date)->locale('ja')->isoFormat('MM/DD(ddd)');
                $start = $attendance->start_time ? Carbon::parse($attendance->start_time)->format('H:i') : '-';
                $end = $attendance->end_time ? Carbon::parse($attendance->end_time)->format('H:i') : '-';
    
                // 休憩時間（分単位で切り捨て）
                $breakMinutes = $attendance->breakLogs->reduce(function ($carry, $log) {
                    if (!$log->break_start || !$log->break_end) return $carry;
                    $start = Carbon::parse($log->break_start)->seconds(0);
                    $end = Carbon::parse($log->break_end)->seconds(0);
                    return $carry + $end->diffInMinutes($start);
                }, 0);
    
                $break = sprintf('%d:%02d', floor($breakMinutes / 60), $breakMinutes % 60);
    
                // 合計勤務時間（休憩を引いた分）
                if ($attendance->start_time && $attendance->end_time) {
                    $workMinutes = Carbon::parse($attendance->end_time)->diffInMinutes(Carbon::parse($attendance->start_time)) - $breakMinutes;
                    $workMinutes = max($workMinutes, 0);
                    $work = sprintf('%d:%02d', floor($workMinutes / 60), $workMinutes % 60);
                } else {
                    $work = '-';
                }
    
                fputcsv($stream, [$date, $start, $end, $break, $work]);
            }
    
            fclose($stream);
        };
    
        return Response::stream($callback, 200, $headers);
    }    
}
