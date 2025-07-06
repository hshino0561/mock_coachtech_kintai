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
        // ユーザー作成
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'name' => 'テストユーザー',
        ]);

        // 勤怠データ作成
        /** @var \App\Models\Attendance $attendance */
        $attendance = Attendance::factory()->create([
            'user_id'    => $user->id,
            'date'       => now()->startOfDay(),
            'start_time' => '08:00:00',
            'end_time'   => '17:00:00',
            'memo'       => 'テストメモ',
        ]);

        // 休憩データ作成
        /** @var \App\Models\BreakLog $break */
        $break = BreakLog::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id'       => $user->id,
            // 'break_date'    => $attendance->date,
            'break_start'   => '12:00:00',
            'break_end'     => '12:30:00',
        ]);

        // 認証状態に設定
        $this->actingAs($user);

        // 勤怠詳細画面にアクセス
        $response = $this->get('/attendance/' . $attendance->id);
        $response->assertStatus(200);

        // 表示確認（文字列の存在確認）
        $response->assertSee($user->name);
        $response->assertSee($attendance->date->format('Y年'));
        $response->assertSee($attendance->date->format('n月j日'));
        $response->assertSee(Carbon::parse($attendance->start_time)->format('H:i'));
        $response->assertSee(Carbon::parse($attendance->end_time)->format('H:i'));
        $response->assertSee(Carbon::parse($break->break_start)->format('H:i'));
        $response->assertSee(Carbon::parse($break->break_end)->format('H:i'));
    }
}
