<?php

namespace Tests\Feature\Attendance;

use Tests\TestCase;
// use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakLog;
use Carbon\Carbon;

class AttendanceDetailFeatureTest extends TestCase
{
    // use RefreshDatabase;

    public function test_10_勤怠詳細情報取得機能()
    {
        /** @var \App\Models\User $user */
        $user = User::whereHas('attendances', function ($q) {
                $q->whereNotNull('date')
                  ->whereNotNull('start_time')
                  ->whereNotNull('end_time')
                  ->whereHas('breakLogs');
            })
            ->latest()
            ->firstOrFail();
    
        $this->assertNotNull($user, 'ユーザー情報が取得できませんでした');
        $this->actingAs($user);
    
        /** @var \App\Models\Attendance|null $attendance */
        $attendance = $user->attendances()
            ->whereNotNull('date')
            ->whereNotNull('start_time')
            ->whereNotNull('end_time')
            ->whereHas('breakLogs')
            ->latest()
            ->first();
    
        $this->assertNotNull($attendance, '勤怠情報が取得できませんでした');
    
        /** @var \App\Models\BreakLog|null $break */
        $break = \App\Models\BreakLog::where('attendance_id', $attendance->id)->first();
        $this->assertNotNull($break, '休憩情報が取得できませんでした');
    
        $response = $this->get('/attendance/' . $attendance->id);
        $response->assertStatus(200);
    
        // 表示確認
        $response->assertSee($user->name);
        $response->assertSee($attendance->date->format('Y年'));
        $response->assertSee($attendance->date->format('n月j日'));
        $response->assertSee(Carbon::parse($attendance->start_time)->format('H:i'));
        $response->assertSee(Carbon::parse($attendance->end_time)->format('H:i'));
        $response->assertSee(Carbon::parse($break->break_start)->format('H:i'));
        $response->assertSee(Carbon::parse($break->break_end)->format('H:i'));
    }
}
