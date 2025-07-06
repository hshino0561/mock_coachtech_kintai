<?php

namespace Tests\Feature\Attendance;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakLog;
use App\Models\Admin;
use App\Models\StampCorrectionRequest;

/** @see \App\Http\Controllers\AttendanceController */
class AttendanceCorrectionFeatureTest extends TestCase
{
    // use RefreshDatabase; // 必要に応じて

    private function actingUserWithAttendance(): array
    {
        /** @var \App\Models\User $user */
        $user = User::whereHas('attendances.breakLogs')
            ->latest()
            ->firstOrFail();

        $this->actingAs($user);

        /** @var \App\Models\Attendance $attendance */
        $attendance = $user->attendances()
            ->whereHas('breakLogs')
            ->latest()
            ->firstOrFail();

        return [$user, $attendance];
    }

    public function test_11_1_出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        // 前提：ログイン・対象勤怠取得済み
        $user = \App\Models\User::whereHas('attendances')->first();
        $this->actingAs($user);

        $attendance = $user->attendances()->first();

        $response = $this
            ->from(route('attendance.detail', $attendance->id))
            ->followingRedirects()
            ->post(route('stamp_correction_request.store', ['attendance' => $attendance->id]), [
                'start_time' => '12:00', // ← 退勤時間より後
                'end_time' => '10:00',
                'memo' => 'テストメモ',
            ]);

        // 表示されたHTML上の文字列としてメッセージを確認
        $response->assertSee('出勤時間もしくは退勤時間が不適切な値です');
    }
    
    public function test_11_2_休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        [$user, $attendance] = $this->actingUserWithAttendance();
    
        $response = $this->from("/attendance/{$attendance->id}")
        ->followingRedirects()
        ->post("/stamp_correction_request/{$attendance->id}", [
            'start_time' => '08:00',
            'end_time'   => '10:00',
            'breaks' => [
                [
                    'start' => '11:00', // 勤務時間外
                    'end'   => '09:30',
                ],
            ],
            'memo' => 'テストメモ',
        ]);
    
        $response->assertSee('休憩時間が勤務時間外です');    
    }
    
    public function test_11_3_休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        [$user, $attendance] = $this->actingUserWithAttendance();
    
        $response = $this->from("/attendance/{$attendance->id}")
            ->followingRedirects()
            ->post("/stamp_correction_request/{$attendance->id}", [
                'start_time' => '08:00',
                'end_time'   => '10:00',
                'breaks' => [
                    [
                        'start' => '09:00',
                        'end'   => '10:30', // → 勤務時間(10:00)より後で不正
                    ],
                ],
                'memo' => 'テストメモ',
            ]);
    
        $response->assertSee('休憩時間が勤務時間外です');
    }
    
    public function test_11_4_備考欄が未入力の場合のエラーメッセージが表示される()
    {
        [$user, $attendance] = $this->actingUserWithAttendance();
    
        $response = $this->from("/attendance/{$attendance->id}")
            ->followingRedirects()
            ->post("/stamp_correction_request/{$attendance->id}", [
                'start_time' => '08:00',
                'end_time'   => '10:00',
                'breaks' => [
                    [
                        'start' => '09:00',
                        'end'   => '09:10', //
                    ],
                ],
                'memo' => '', // 未入力
            ]);
    
        $response->assertSee('備考を記入してください');
    }

    public function test_11_5_修正申請処理が行われる()
    {
        [$user, $attendance] = $this->actingUserWithAttendance();

        $response = $this
            ->from(route('attendance.detail', $attendance->id))
            ->post(route('stamp_correction_request.store', ['attendance' => $attendance->id]), [
                'start_time' => '08:00',
                'end_time'   => '10:00',
                'breaks' => [
                    [
                        'start' => '08:30',
                        'end'   => '08:50',
                    ],
                ],
                'memo' => '修正申請テスト',
            ]);
    
        $response->assertRedirect("/attendance?id={$attendance->id}");
    
        $this->assertDatabaseHas('stamp_correction_requests', [
            'attendance_id' => $attendance->id,
            'status' => 'pending',
            'memo' => '修正申請テスト',
        ]);
    }

    public function test_11_6_承認待ちにログインユーザーが行った申請が全て表示されていること()
    {
        [$user, $attendance] = $this->actingUserWithAttendance();
    
        // 修正申請①
        $this->post("/stamp_correction_request/{$attendance->id}", [
            'start_time' => '08:00',
            'end_time' => '10:00',
            'memo' => '修正申請A',
        ]);
    
        // 修正申請②（別の勤怠レコードを使う）
        $anotherAttendance = \App\Models\Attendance::factory()->create(['user_id' => $user->id]);
        $this->post("/stamp_correction_request/{$anotherAttendance->id}", [
            'start_time' => '09:00',
            'end_time' => '11:00',
            'memo' => '修正申請B',
        ]);
    
        // 申請一覧（承認待ちタブ）へアクセス
        $response = $this->get('/stamp_correction_request/list?status=pending');
    
        // 各申請内容が表示されているかチェック
        $response->assertStatus(200);
        $response->assertSee('修正申請A');
        $response->assertSee('修正申請B');
    }

    public function test_11_7_承認済みに管理者が承認した修正申請が全て表示される()
    {
        // 一般ユーザーでログイン
        [$user, $attendance] = $this->actingUserWithAttendance();

        // 該当勤怠を取得（既存レコード前提）
        $attendance = Attendance::where('user_id', $user->id)->latest()->firstOrFail();

        // 修正申請データを作成
        $request = StampCorrectionRequest::create([
            'user_id'        => $user->id,
            'attendance_id'  => $attendance->id,
            'date'           => $attendance->date,
            'start_time'     => '08:00',
            'end_time'       => '10:00',
            'memo'           => '修正申請テスト',
            'status'         => 'pending',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        // ★ 承認済みに更新（今回の目的）
        $request->update(['status' => 'approved']);

        // 承認済みタブへアクセス
        $response = $this->get('/stamp_correction_request/list?status=approved');
        $response->assertStatus(200);
        $response->assertSee('承認済み');
        $response->assertSee(str_replace([' ', '　'], '', $user->name));
        $response->assertSee('修正申請テスト');
    }

    public function test_11_8_各申請の詳細を押下すると申請詳細画面に遷移する()
    {
        // ユーザー作成 & ログイン
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);
    
        // 勤怠データ作成
        $attendance = \App\Models\Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-07-05',
        ]);
    
        // 修正申請データ作成（status: pending）
        $request = \App\Models\StampCorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'start_time' => '08:00',
            'end_time'   => '10:00',
            'memo'       => '詳細遷移テスト',
            'status'     => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    
        // 一覧ページにアクセス（承認待ち）
        $response = $this->get('/stamp_correction_request/list?status=pending');
        $response->assertStatus(200);
    
        // 詳細リンクが存在するか確認
        $response->assertSee('詳細');
        $response->assertSee(route('stamp_correction_request.show_detail', ['stamp_correction_request' => $request->id]));
    
        // 詳細ページへ遷移
        $detailResponse = $this->get(route('stamp_correction_request.show_detail', ['stamp_correction_request' => $request->id]));
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('詳細遷移テスト'); // 備考欄など
    }
}
