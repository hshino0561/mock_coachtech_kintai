<?php

namespace Tests\Feature\Attendance;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkStatusDisplayTest extends TestCase
{
    // use RefreshDatabase;

    private function createVerifiedUserWithStatus(string $status): User
    {
        /** @var \App\Models\User $user */
        $user = User::firstOrCreate(
            ['email' => "status_test_{$status}@example.com"],
            [
                'name' => "テスト_{$status}",
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
                'is_first_login' => false,
                'work_status' => $status,
            ]
        );

        // 念のため上書き保存
        $user->work_status = $status;
        $user->email_verified_at = now();
        $user->save();

        return $user;
    }

    public function test_5_1_勤務外の場合、勤怠ステータスが正しく表示される()
    {
        $user = $this->createVerifiedUserWithStatus('before_work');
        $this->actingAs($user);
        $response = $this->get('/attendance');

        $response->assertSee('勤務外');
    }

    public function test_5_2_出勤中の場合、勤怠ステータスが正しく表示される()
    {
        $user = $this->createVerifiedUserWithStatus('on_work');
        $this->actingAs($user);
        $response = $this->get('/attendance');

        $response->assertSee('出勤中');
    }

    public function test_5_3_休憩中の場合、勤怠ステータスが正しく表示される()
    {
        $user = $this->createVerifiedUserWithStatus('on_break');
        $this->actingAs($user);
        $response = $this->get('/attendance');

        $response->assertSee('休憩中');
    }

    public function test_5_4_退勤済の場合、勤怠ステータスが正しく表示される()
    {
        // ユーザー作成（※ status: after_work）
        $user = $this->createVerifiedUserWithStatus('after_work');
    
        // 当日の出勤・退勤済レコードを作成しておく
        \App\Models\Attendance::create([
            'user_id'    => $user->id,
            'date'       => today(),
            'start_time' => now()->setTime(9, 0),
            'end_time'   => now()->setTime(18, 0),
        ]);
    
        // ログイン & アクセス
        $this->actingAs($user);
        $response = $this->get('/attendance');
    
        // 表示確認
        $response->assertSee('退勤済');
    }
}
