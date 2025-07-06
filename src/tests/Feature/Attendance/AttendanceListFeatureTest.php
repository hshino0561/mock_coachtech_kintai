<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceListFeatureTest extends TestCase
{
    // use RefreshDatabase;

    /**
     * 自分が行った勤怠情報が全て表示されている
     */
    public function test_9_1_自分が行った勤怠情報が全て表示されている()
    {
        // ① ユーザー作成
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // ② 勤怠データを3件作成（2025年7月）
        $attendances = collect([
            [
                'date' => '2025-07-01',
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'memo' => 'テストメモ 1',
            ],
            [
                'date' => '2025-07-02',
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'memo' => 'テストメモ 2',
            ],
            [
                'date' => '2025-07-03',
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'memo' => 'テストメモ 3',
            ],
        ]);

        foreach ($attendances as $attendance) {
            Attendance::create(array_merge($attendance, [
                'user_id' => $user->id,
            ]));
        }

        // ③ ログイン
        $this->actingAs($user);

        // ④ 勤怠一覧画面にアクセス
        $response = $this->get('/attendance/list?month=2025-07');

        // ⑤ 勤怠データの検証
        foreach ($attendances as $attendance) {
            $response->assertSee(Carbon::parse($attendance['date'])->format('m/d'));
            $response->assertSee(substr($attendance['start_time'], 0, 5)); // "09:00"
            $response->assertSee(substr($attendance['end_time'], 0, 5));   // "18:00"
            // $response->assertSee($attendance['memo']);
        }

        // ⑥ 表示されている月が「2025/07」であること
        $response->assertSee('2025/07');

        // ⑦ 詳細リンクの確認（1件でもOK）
        $latest = Attendance::where('user_id', $user->id)->first();
        $response->assertSee((string) $latest->id);
    }

    /**
     * 勤怠一覧画面に遷移した際に現在の月が表示される
     */
    public function test_9_2_勤怠一覧画面に遷移した際に現在の月が表示される()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        $response = $this->get('/attendance/list');
        $response->assertSee(now()->format('Y/m'));
    }

    /**
     * 前月を押下した時に表示月の前月の情報が表示される
     */
    public function test_9_3_前月を押下した時に表示月の前月の情報が表示される()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        $prevMonth = now()->subMonth()->startOfMonth();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $prevMonth,
        ]);

        $response = $this->get('/attendance/list?month=' . $prevMonth->format('Y-m'));
        $response->assertSee($prevMonth->format('Y/m'));
        $response->assertSee($attendance->date->format('m/d')); // 例: "06/01"
    }

    /**
     * 翌月を押下した時に表示月の前月の情報が表示される
     */
    public function test_9_4_翌月を押下した時に表示月の前月の情報が表示される()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        $nextMonth = now()->addMonth()->startOfMonth();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $nextMonth,
        ]);

        $response = $this->get('/attendance/list?month=' . $nextMonth->format('Y-m'));
        $response->assertSee($nextMonth->format('Y/m'));
        $response->assertSee($attendance->date->format('m/d'));
    }

    /**
     * 詳細を押下すると、その日の勤怠詳細画面に遷移する
     */
    public function test_9_5_詳細を押下すると、その日の勤怠詳細画面に遷移する()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::parse('2025-07-01'), // または now() など
        ]);

        $response = $this->get('/attendance/' . $attendance->id);
        $response->assertStatus(200);
        $this->assertNotNull($attendance->date, 'Attendance date should not be null');
        $response->assertSee($attendance->date->format('Y年'));
        $response->assertSee($attendance->date->format('n月j日'));        
    }
}
