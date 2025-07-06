<?php

namespace Tests\Feature\Attendance;

use App\Models\Admin;
use App\Models\StampCorrectionRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AdminCorrectionRequestFeatureTest extends TestCase
{
    // use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::where('email', 'admin1@admin.com')->firstOrFail();
    }

    public function test_15_1_承認待ちの修正申請が全て表示されている()
    {
        /** @var \App\Models\Admin $admin */
        $admin = \App\Models\Admin::where('email', 'admin1@admin.com')->first();
    
        // 承認待ちの修正申請を取得
        /** @var \App\Models\StampCorrectionRequest $request */
        $request = \App\Models\StampCorrectionRequest::where('status', 'pending')
            ->get();
        
        $response = $this->actingAs($admin, 'admin')->get('/stamp_correction_request/list?status=pending');
    
        $response->assertOk();
        $response->assertSee('承認待ち');
    }
    
    public function test_15_2_承認済みの修正申請が全て表示されている()
    {
        /** @var \App\Models\Admin $admin */
        $admin = \App\Models\Admin::where('email', 'admin1@admin.com')->first();
    
        // 承認済みの修正申請を取得
        /** @var \App\Models\StampCorrectionRequest $request */
        $request = \App\Models\StampCorrectionRequest::where('status', 'approved')
            ->get();
        
        $response = $this->actingAs($admin, 'admin')->get('/stamp_correction_request/list?status=approved');
    
        $response->assertOk();
        $response->assertSee('承認済み');
    }

    public function test_15_3_修正申請の詳細内容が正しく表示されている()
    {
        $request = StampCorrectionRequest::with('user')
            ->where('status', 'pending')
            ->latest()
            ->firstOrFail();

        $response = $this->actingAs($this->admin, 'admin')
            ->get("/stamp_correction_request/approve/{$request->id}");

            $response->assertSee($request->user->name);
            $response->assertSee(optional($request->start_time)->format('H:i'));
            $response->assertSee(optional($request->end_time)->format('H:i'));
            $response->assertSee($request->memo);
    }

    public function test_15_4_修正申請の承認処理が正しく行われる()
    {
        /** @var \App\Models\Admin $admin */
        $admin = \App\Models\Admin::where('email', 'admin1@admin.com')->first();

        /** @var \App\Models\StampCorrectionRequest $request */
        $request = \App\Models\StampCorrectionRequest::where('status', 'pending')->latest()->firstOrFail();

        $attendance = $request->attendance;

        $response = $this->actingAs($admin, 'admin')
            ->from("/stamp_correction_request/approve/{$request->id}")
            ->patch("/stamp_correction_request/approve/{$request->id}", [
                'start_time' => optional($request->start_time)->format('H:i'),
                'end_time'   => optional($request->end_time)->format('H:i'),
                'memo'       => $request->memo,
                'breaks'     => [], // 必要に応じて
                'action'     => 'approve', // ★重要
            ]);

        // 明示的なリダイレクトURLではなく、表示内容で確認する
        $followUp = $this->actingAs($admin, 'admin')
            ->get('/stamp_correction_request/list');

        $followUp->assertOk();
        $followUp->assertSee('承認済み');

        // DBに反映されたことを確認
        $this->assertDatabaseHas('stamp_correction_requests', [
            'id'     => $request->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('attendances', [
            'id'         => $attendance->id,
            'start_time' => Carbon::parse($request->start_time)->format('H:i:s'),
            'end_time'   => Carbon::parse($request->end_time)->format('H:i:s'),
            'memo'       => $request->memo,
        ]);
    }
}
