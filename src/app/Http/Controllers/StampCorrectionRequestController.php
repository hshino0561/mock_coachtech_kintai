<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Attendance;
use App\Models\User;
use App\Models\Admin;
use App\Models\StampCorrectionRequest;
use App\Models\BreakCorrectionRequest;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreStampCorrectionRequest;

class StampCorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        \Log::debug('セッションID: ' . session()->getId());
        \Log::debug('Auth::user()', ['user' => Auth::user()]);
        \Log::debug('Auth::guard(admin)', ['admin' => Auth::guard('admin')->user()]);
    
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
    
        /** @var \App\Models\Admin|null $admin */
        $admin = Auth::guard('admin')->user();
    
        if (! $user && ! $admin) {
            \Log::debug('未認証：強制リダイレクト');
            return redirect('/login');
        }
    
        $status = $request->get('status', 'pending');
    
        // 管理者：全申請対象
        if ($admin) {
            $requests = \App\Models\StampCorrectionRequest::with(['user', 'attendance'])
                ->where('status', $status)
                ->latest()
                ->get();
        
            return view('admin.pg12_admin_stamp_correction_list', [
                'requests' => $requests,
            ]);
        }
            
        // 一般ユーザー：自分の申請のみ（明示的にuser_idを指定）
        if ($user) {
            $requests = \App\Models\StampCorrectionRequest::with(['user', 'attendance'])
                ->where('user_id', $user->id)
                ->where('status', $status)
                ->latest()
                ->get();
    
            return view('pg06_stamp_correction_list', [
                'requests' => $requests,
            ]);
        }
    
        abort(403, 'Unauthorized access');
    }

    /**
     * 修正申請の登録
     * @param \Illuminate\Http\Request $request
     * @param int $attendanceId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreStampCorrectionRequest $request, int $attendanceId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
    
        $attendance = Attendance::with('breakLogs')->findOrFail($attendanceId);
    
        // 親の修正申請（出退勤時間など）
        $correction = StampCorrectionRequest::create([
            'attendance_id'   => $attendanceId,
            'user_id'         => $user->id,
            'attendance_date' => $attendance->date,
            'start_time'      => $request->start_time,
            'end_time'        => $request->end_time,
            'memo'            => $request->memo,
            'status'          => 'pending',
        ]);
    
        // 子テーブルとして休憩情報を登録（複数対応）
        foreach ($request->input('breaks', []) as $break) {
            if (!empty($break['start']) && !empty($break['end'])) {
                $correction->breakCorrectionRequests()->create([
                    'break_start' => $break['start'],
                    'break_end'   => $break['end'],
                ]);
            }
        }
    
        return redirect()->route('attendance.show', ['id' => $attendanceId])
            ->with('success', '修正申請が送信されました。');
    }

    public function showDetail(StampCorrectionRequest $stamp_correction_request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
    
        // 該当申請に紐づく休憩修正申請を取得
        $breaks = BreakCorrectionRequest::where('stamp_correction_request_id', $stamp_correction_request->id)->get();

        // 空の1行を追加（bladeで自動的に表示される）
        $breaks->push((object)[
            'break_start' => null,
            'break_end'   => null,
        ]);

        return view('pg05_attendance_detail', [
            'attendance'    => $stamp_correction_request,
            'user'          => $user,
            'readonly'      => true,
            'from_request'  => true,
            'breaks'        => $breaks,
        ]);
    }
}