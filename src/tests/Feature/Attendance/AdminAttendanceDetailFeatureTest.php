<?php

namespace Tests\Feature\Attendance;

use Tests\TestCase;
// use RefreshDatabase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\WithFaker;

class AdminAttendanceDetailFeatureTest extends TestCase
{
    use WithFaker;

    /**
     * 管理者ログイン処理
     */
    private function actingAsAdmin()
    {
        $admin = Admin::where('email', 'admin1@admin.com')->first();
        $this->assertNotNull($admin, '管理者ユーザーが存在しません');
        return $this->actingAs($admin, 'admin');
    }

    /**
     * ① 勤怠詳細が正しく表示される
     */
    public function test_13_1_勤怠詳細画面に表示されるデータが選択したものになっている()
    {
        $this->actingAsAdmin();
    
        // 既存データの中から確認対象を取得（例：id=111 の勤怠データ）
        /** @var \App\Models\Attendance $attendance */
        $attendance = \App\Models\Attendance::with('user')->findOrFail(111);
    
        // アクセス
        $response = $this->get("/admin/attendance/{$attendance->id}");
    
        $response->assertStatus(200);
    
        // 勤怠詳細画面に正しい情報が表示されていることを確認
        $response->assertSee(str_replace(' ', '', $attendance->user->name));
        if ($attendance->start_time) {
            $response->assertSee(\Carbon\Carbon::parse($attendance->start_time)->format('H:i'));
        }
        if ($attendance->end_time) {
            $response->assertSee(\Carbon\Carbon::parse($attendance->end_time)->format('H:i'));
        }
        if ($attendance->memo) {
            $response->assertSee($attendance->memo);
        }
    }

    /**
     * ② 出勤 > 退勤 の場合にエラー表示
     */
    public function test_13_2_出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        $this->actingAsAdmin();

        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->patch("/admin/attendance/{$attendance->id}", [
            'start_time' => '20:00',
            'end_time' => '18:00',
            'memo' => 'テスト備考',
        ]);

        $response->assertSessionHasErrors([
            'start_time' => '出勤時間もしくは退勤時間が不適切な値です。',
        ]);
    }

    /**
     * ③ 休憩開始 > 退勤 の場合にエラー表示
     */
    public function test_13_3_休憩開始時間が退勤時間より後になっている場合_エラーメッセージが表示される()
    {
        /** @var \App\Models\Admin $admin */
        $admin = \App\Models\Admin::where('email', 'admin1@admin.com')->first();
    
        /** @var \App\Models\Attendance $attendance */
        $attendance = \App\Models\Attendance::whereNotNull('end_time')->latest()->firstOrFail();
    
        $startTime     = \Carbon\Carbon::parse($attendance->start_time)->format('H:i');
        $endTime       = \Carbon\Carbon::parse($attendance->end_time)->format('H:i');
        $afterEndTime  = \Carbon\Carbon::parse($attendance->end_time)->addMinutes(30)->format('H:i');
        $afterEndEnd   = \Carbon\Carbon::parse($attendance->end_time)->addMinutes(60)->format('H:i');
    
        $response = $this->actingAs($admin, 'admin')
            ->from("/admin/attendance/{$attendance->id}")
            ->followingRedirects()
            ->patch("/admin/attendance/{$attendance->id}", [
                'start_time' => $startTime,
                'end_time'   => $endTime,
                'breaks'     => [
                    ['start' => $afterEndTime, 'end' => $afterEndEnd],
                ],
                'memo' => 'テスト備考',
            ]);
    
        // バリデーションメッセージの表示を確認
        $response->assertSee('休憩時間が勤務時間外です。');
    }
        
    /**
     * ④ 休憩終了 > 退勤 の場合にエラー表示
     */
    public function test_13_4_休憩終了時間が退勤時間より後になっている場合_エラーメッセージが表示される()
    {
        /** @var \App\Models\Admin $admin */
        $admin = \App\Models\Admin::where('email', 'admin1@admin.com')->first();
    
        /** @var \App\Models\Attendance $attendance */
        $attendance = \App\Models\Attendance::whereNotNull('end_time')->latest()->firstOrFail();
    
        $startTime     = \Carbon\Carbon::parse($attendance->start_time)->format('H:i');
        $endTime       = \Carbon\Carbon::parse($attendance->end_time)->format('H:i');
        $beforeEnd     = \Carbon\Carbon::parse($attendance->end_time)->subMinutes(30)->format('H:i');
        $afterEndTime  = \Carbon\Carbon::parse($attendance->end_time)->addMinutes(30)->format('H:i');
    
        $response = $this->actingAs($admin, 'admin')
            ->from("/admin/attendance/{$attendance->id}")
            ->followingRedirects()
            ->patch("/admin/attendance/{$attendance->id}", [
                'start_time' => $startTime,
                'end_time'   => $endTime,
                'breaks'     => [
                    ['start' => $beforeEnd, 'end' => $afterEndTime], // 終了時刻が退勤後 → NG
                ],
                'memo' => 'テスト備考',
            ]);
    
        // バリデーションメッセージの表示を確認
        $response->assertSee('休憩時間が勤務時間外です。');
    }

    /**
     * ⑤ 備考欄が未入力でエラー
     */
    public function test_13_5_備考が未入力でエラー()
    {
        $this->actingAsAdmin();

        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'start_time' => '09:00',
            'end_time' => '18:00',
        ]);

        $response = $this->patch("/admin/attendance/{$attendance->id}", [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'memo' => '',
        ]);

        $response->assertSessionHasErrors([
            'memo' => '備考を記入してください。',
        ]);
    }
}
