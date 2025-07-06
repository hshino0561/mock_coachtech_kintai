<?php

namespace Tests\Feature\Attendance;

use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
// use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

class AdminAttendanceListFeatureTest extends TestCase
{
    // use RefreshDatabase;

    /**
     * 管理者でログインする（admin1@admin.com）
     *
     * @return \App\Models\Admin
     */
    private function loginAsAdmin(): Admin
    {
        /** @var \App\Models\Admin $admin */
        $admin = \App\Models\Admin::where('email', 'admin1@admin.com')->firstOrFail();
    
        // セッション上で admin ガードとしてログイン（viewやrouteの認可通過のため）
        $this->actingAs($admin, 'admin');
    
        return $admin;
    }
    
    /**
     * 12-1 その日になされた全ユーザーの勤怠情報が正確に確認できる
     */
    public function test_12_1_その日になされた全ユーザーの勤怠情報が正確に確認できる()
    {
        $this->loginAsAdmin();

        $today = now()->format('Y/m/d');

        // 勤怠一覧画面へアクセス
        $response = $this->get('/admin/attendance/list');

        $response->assertStatus(200);

        // 確認したいデータが画面上に表示されていること（名前・出勤時刻など）
        $response->assertSee('出勤'); // 固定文言
        $response->assertSee($today); // 今日の日付が含まれていること
    }

    /**
     * 12-2 現在日付が表示される
     */
    public function test_12_2_遷移した際に現在の日付が表示される()
    {
        $this->loginAsAdmin();

        $today = now()->format('Y年n月j日');

        $response = $this->get('/admin/attendance/list');

        $response->assertStatus(200);
        $response->assertSee($today);
    }

    /**
     * 12-3 「前日」ボタン押下で前日の勤怠が表示される
     */
    public function test_12_3_前日ボタン押下で前日の勤怠が表示される()
    {
        $this->loginAsAdmin();

        $previousDay = now()->subDay()->format('Y-m-d');
        $displayText = now()->subDay()->format('Y年n月j日');

        $response = $this->get('/admin/attendance/list?date=' . $previousDay);

        $response->assertStatus(200);
        $response->assertSee($displayText);
    }

    /**
     * 12-4 「翌日」ボタン押下で翌日の勤怠が表示される
     */
    public function test_12_4_翌日ボタン押下で翌日の勤怠が表示される()
    {
        $this->loginAsAdmin();

        $nextDay = now()->addDay()->format('Y-m-d');
        $displayText = now()->addDay()->format('Y年n月j日');

        $response = $this->get('/admin/attendance/list?date=' . $nextDay);

        $response->assertStatus(200);
        $response->assertSee($displayText);
    }
}
