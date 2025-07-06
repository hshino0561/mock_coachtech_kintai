<?php

namespace Tests\Feature\Attendance;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BreakFeatureTest extends TestCase
{
    // use RefreshDatabase;

    /**
     * 休憩ボタンが正しく機能する
     */
    public function test_7_1_休憩ボタンが正しく機能される()
    {
        // ステータス「before_work」のユーザー作成
        $user = $this->createVerifiedUserWithStatus('before_work');
        $this->actingAs($user);
    
        // 出勤処理
        $this->post('/attendance/start');
    
        // 勤怠登録画面を取得（休憩ボタンが出る画面）
        $response = $this->get('/attendance');
    
        // 「休憩入」ボタンが表示されていることを確認
        $response->assertSee('休憩入');
    
            // 休憩開始処理
            $this->post('/attendance/break/start');
        
            // ステータスが「休憩中」に変化していること
            $user->refresh();
            $this->assertEquals('on_break', $user->work_status);
    }

    /**
     * 休憩は一日に何回でもできる
     */
    public function test_7_2_休憩は一日に何回でもできる()
    {
        // 休憩中のユーザーをDBから取得（更新が最新のもの）
        $user = User::where('work_status', 'on_break')
            ->orderByDesc('updated_at')
            ->firstOrFail();
    
        // ① 1回目の休憩終了（休憩戻り）
        $this->actingAs($user);
        $this->post('/attendance/break/end');
        $user->refresh();
        $this->assertEquals('on_work', $user->work_status);
    
        // ② 2回目の休憩開始
        $this->post('/attendance/break/start');
        $user->refresh();
        $this->assertEquals('on_break', $user->work_status);
    
        // ③ 2回目の休憩終了（休憩戻り）
        $this->post('/attendance/break/end');
        $user->refresh();
        $this->assertEquals('on_work', $user->work_status);
    
        // 「休憩入」ボタンが表示されていることを確認
        $response = $this->get('/attendance');
        $response->assertSee('休憩入');
    }

    /**
     * 休憩戻ボタンが正しく機能する
     */
    public function test_7_3_休憩戻ボタンが正しく機能する()
    {
        $user = $this->createVerifiedUserWithStatus('on_work');
        $this->actingAs($user);

    // 出勤処理を事前に実行（出勤レコードとステータスが必要）
    $this->post('/attendance/start');

        // 休憩に入る
        $this->post('/attendance/break/start');
        $user->refresh();
        $this->assertEquals('on_break', $user->work_status);

        // 戻る処理
        $this->post('/attendance/break/end');
        $user->refresh();
        $this->assertEquals('on_work', $user->work_status);
    }

    /**
     * 休憩戻は一日に何回でもできる
     */
    public function test_7_4_休憩戻は一日に何回でもできる()
    {
        // ユーザー作成＆出勤処理
        $user = $this->createVerifiedUserWithStatus('before_work');
        $this->actingAs($user);
        $this->post('/attendance/start');
        $user->refresh();
        $this->assertEquals('on_work', $user->work_status);
    
        // 1回目の休憩 → 戻り
        $this->post('/attendance/break/start');
        $user->refresh();
        $this->assertEquals('on_break', $user->work_status);
    
        $this->post('/attendance/break/end');
        $user->refresh();
        $this->assertEquals('on_work', $user->work_status);
    
        // 2回目の休憩
        $this->post('/attendance/break/start');
        $user->refresh();
        $this->assertEquals('on_break', $user->work_status);
    
        // 休憩戻ボタンが再度表示されているか確認（HTMLに含まれているか）
        $response = $this->get('/attendance');
        $response->assertSee('休憩戻');
    }

    /**
     * 休憩時間が勤怠一覧画面で確認できる
     */
    public function test_7_5_休憩時間が勤怠一覧画面で確認できる()
    {
        // ① ユーザー作成 & 出勤
        $user = $this->createVerifiedUserWithStatus('before_work');
        $this->actingAs($user);
    
        $this->post('/attendance/start');
        $user->refresh();
        $this->assertEquals('on_work', $user->work_status);
    
        // ② 休憩 → 戻り
        $this->post('/attendance/break/start');
        $user->refresh();
        $this->assertEquals('on_break', $user->work_status);
    
        $this->post('/attendance/break/end');
        $user->refresh();
        $this->assertEquals('on_work', $user->work_status);
    
        // ③ 勤怠一覧画面で休憩時間が表示されているか（00:で始まる想定）
        $response = $this->get('/attendance/list');
        $response->assertSee('00:'); // 例: 00:01 など
    }

    /**
     * ユーザー生成用：認証済み + 勤務状態付き
     *
     * @param string $status
     * @return \App\Models\User
     */
    private function createVerifiedUserWithStatus(string $status): User
    {
        return User::factory()->create([
            'email_verified_at' => now(),
            'work_status' => $status,
        ]);
    }
}
