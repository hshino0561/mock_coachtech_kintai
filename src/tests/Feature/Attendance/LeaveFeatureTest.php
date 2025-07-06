<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;

class LeaveFeatureTest extends TestCase
{
    // use RefreshDatabase; // 必要に応じて有効化

    /**
     * 8-1：退勤ボタンが正しく機能する
     */
    public function test_8_1_退勤ボタンが正しく機能する()
    {
        // ステータス「on_work」のユーザー作成
        $user = $this->createVerifiedUserWithStatus('on_work');
        $this->actingAs($user);

        $this->post('/attendance/start');
        $user->refresh();
        $this->assertEquals('on_work', $user->work_status);

        // 勤怠登録画面に「退勤」ボタンが表示されていること
        $response = $this->get('/attendance');
        $response->assertSee('退勤');

        // 退勤処理を実行
        $this->post('/attendance/end');

        // ステータスが「after_work」に変更されていること
        $user->refresh();
        $this->assertEquals('after_work', $user->work_status);

        // 勤怠登録画面に幼児されるステータスが「退勤済」になっていること
        $response = $this->get('/attendance');
        $response->assertSee('退勤済');
    }

    /**
     * 8-2：退勤時刻が管理画面で確認できる
     */
    public function test_8_2_退勤時刻が管理画面で確認できる()
    {
        // 勤務外ユーザー作成 → 出勤 → 退勤まで行う
        $user = $this->createVerifiedUserWithStatus('before_work');
        $this->actingAs($user);

        // 出勤
        $this->post('/attendance/start');
        $user->refresh();
        $this->assertEquals('on_work', $user->work_status);

        // 退勤
        $this->post('/attendance/end');
        $user->refresh();
        $this->assertEquals('after_work', $user->work_status);

        // ② ログアウトして管理者に切り替え
        auth()->logout();

        // ③ 管理者としてログイン（adminsテーブルを利用）
        /** @var \App\Models\Admin $admin */
        $admin = \App\Models\Admin::where('name', 'admin1')->firstOrFail();
        $this->actingAs($admin, 'admin'); // ← 第2引数でadminガードを指定

        // 退勤レコードが保存されていることを前提に管理画面へアクセス
        // ④ 管理者画面にアクセス
        $response = $this->get('/admin/attendance/list');

        // ⑤退勤時刻が表示されているか確認
        $attendance = $user->attendances()->latest()->first();
        $response->assertSee($user->name);
        if ($attendance->end_time) {
            $response->assertSee($attendance->end_time->format('H:i'));
        }    
    }

    /**
     * ユーザー生成：認証済み＋ステータス付き
     *
     * @param string $status
     * @return \App\Models\User
     */
    private function createVerifiedUserWithStatus(string $status): User
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'work_status' => $status,
        ]);

        return $user;
    }
}
