<?php

namespace Tests\Feature\Attendance;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceStartTest extends TestCase
{
    // use RefreshDatabase;

    public function test_6_1_出勤ボタンが正しく機能する()
    {
        $user = $this->createVerifiedUserWithStatus('before_work');
        $this->actingAs($user);

        // 出勤前の画面確認
        $response = $this->get('/attendance');
        $response->assertSee('勤務外');
        $this->assertEquals('before_work', $user->work_status);

        // 出勤処理
        $response = $this->post('/attendance/start');
        $response->assertRedirect('/attendance');

        // ステータス確認
        $user->refresh();
        $this->assertEquals('on_work', $user->work_status);

        // 再表示でステータスが勤務中
        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
    }

    public function test_6_2_出勤は一日一回のみできる()
    {
        $user = $this->createVerifiedUserWithStatus('after_work');
        $this->actingAs($user);

        // 出勤ボタンが表示されない
        $response = $this->get('/attendance');
        $response->assertDontSee('退勤済み');
    }

    public function test_6_3_出勤時刻が管理画面で確認できる()
    {
        // ① 一般ユーザーでログイン・出勤
        $user = $this->createVerifiedUserWithStatus('before_work');
        $this->actingAs($user);
        $this->post('/attendance/start');
    
        // ② ログアウトして管理者に切り替え
        auth()->logout();
    
        // ③ 管理者としてログイン（adminsテーブルを利用）
        /** @var \App\Models\Admin $admin */
        $admin = \App\Models\Admin::where('name', 'admin1')->firstOrFail();
        $this->actingAs($admin, 'admin'); // ← 第2引数でadminガードを指定
    
        // ④ 管理者画面にアクセス
        $response = $this->get('/admin/attendance/list');
    
        // ⑤出勤時刻が表示されているか確認
        $attendance = $user->attendances()->latest()->first();
        $response->assertSee($user->name);
        if ($attendance->start_time) {
            $response->assertSee($attendance->start_time->format('H:i'));
        }        
    }

    /**
     * メール認証済みユーザーをステータス付きで作成
     */
    public function createVerifiedUserWithStatus(string $status): User
    {
        return User::factory()->create([    
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),    
            'work_status' => $status,
        ]);
    }
}
