<?php

namespace Tests\Feature\Attendance;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;

class AdminStaffFeatureTest extends TestCase
{
    // use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::where('email', 'admin1@admin.com')->firstOrFail();
    }

    public function test_14_1_管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get('/admin/staff/list');

        $users = User::all();

        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
        }
    }

    public function test_14_2_ユーザーの勤怠情報が正しく表示される()
    {
        $user = User::has('attendances')->firstOrFail();
        $attendance = $user->attendances()->latest()->first();

        $response = $this->actingAs($this->admin, 'admin')
            ->get("/admin/attendance/staff/{$user->id}");

        $response->assertSee($attendance->date->format('m/d'));

        if ($attendance->start_time) {
            $response->assertSee(Carbon::parse($attendance->start_time)->format('H:i'));
        }

        if ($attendance->end_time) {
            $response->assertSee(Carbon::parse($attendance->end_time)->format('H:i'));
        }
    }

    public function test_14_3_前月を押下した時に表示月の前月の情報が表示される()
    {
        $user = User::has('attendances')->first();
        $baseDate = Carbon::today()->startOfMonth();

        $response = $this->actingAs($this->admin, 'admin')
            ->get("/admin/attendance/staff/{$user->id}?month=" . $baseDate->format('Y-m'));

        $prevMonth = $baseDate->copy()->subMonth()->format('Y/m');
        $response = $this->get("/admin/attendance/staff/{$user->id}?month=" . $baseDate->copy()->subMonth()->format('Y-m'));

        $response->assertSee($prevMonth);
    }

    public function test_14_4_翌月を押下した時に表示月の前月の情報が表示される()
    {
        $user = User::has('attendances')->first();
        $baseDate = Carbon::today()->startOfMonth();

        $response = $this->actingAs($this->admin, 'admin')
            ->get("/admin/attendance/staff/{$user->id}?month=" . $baseDate->format('Y-m'));

        $nextMonth = $baseDate->copy()->addMonth()->format('Y/m');
        $response = $this->get("/admin/attendance/staff/{$user->id}?month=" . $baseDate->copy()->addMonth()->format('Y-m'));

        $response->assertSee($nextMonth);
    }

    public function test_14_5_詳細を押下するとその日の勤怠詳細画面に遷移する()
    {
        /** @var \App\Models\Admin $admin */
        $admin = \App\Models\Admin::where('email', 'admin1@admin.com')->firstOrFail();
    
        // start_time, end_time が入っていて、日付が新しい勤怠を1件取得
        /** @var \App\Models\Attendance $attendance */
        $attendance = \App\Models\Attendance::with('user')
            ->whereNotNull('start_time')
            ->whereNotNull('end_time')
            ->orderByDesc('date')
            ->firstOrFail();
    
        /** @var \App\Models\User $user */
        $user = $attendance->user;
    
        // 該当月（最新勤怠の月）でスタッフ別勤怠一覧を表示
        $monthParam = \Carbon\Carbon::parse($attendance->date)->format('Y-m');
        $response = $this->actingAs($admin, 'admin')
            ->get("/admin/attendance/staff/{$user->id}?month={$monthParam}");
    
        $response->assertStatus(200);
        $response->assertSee('/admin/attendance/' . $attendance->id);
    }
}