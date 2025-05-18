<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;

class StampCorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $status = $request->get('status', 'pending');
    
        $requests = StampCorrectionRequest::with('user')
            ->where('user_id', $user->id)
            ->where('status', $status)
            ->orderByDesc('created_at')
            ->get();
    
        return view('pg06_stamp_correction_list', compact('requests'));
    }    

    /**
     * 修正申請の登録
     * @param \Illuminate\Http\Request $request
     * @param int $attendanceId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, int $attendanceId)
    {
        // $request->validate([
        //     'start_time' => 'nullable|date_format:H:i',
        //     'end_time' => 'nullable|date_format:H:i',
        //     'break_start' => 'nullable|date_format:H:i',
        //     'break_end' => 'nullable|date_format:H:i',
        //     'memo' => 'nullable|string|max:255',
        // ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 勤怠IDから勤怠レコードを取得
        $attendance = Attendance::findOrFail($attendanceId);

        // 修正申請の登録
        StampCorrectionRequest::create([
            'attendance_id'   => $attendanceId,
            'user_id'         => $user->id,
            'attendance_date' => $attendance->date,  // ← 勤怠の日付を追加
            'start_time'      => $request->start_time,
            'end_time'        => $request->end_time,
            'break_start'     => $request->break_start,
            'break_end'       => $request->break_end,
            'memo'            => $request->memo,
            'reason'          => $request->memo,
            'status'          => 'pending',
        ]);        

        return redirect()->route('attendance.show', ['id' => $attendanceId])
            ->with('success', '修正申請が送信されました。');
    }
}
