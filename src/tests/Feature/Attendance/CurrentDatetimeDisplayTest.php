<?php

namespace Tests\Feature\Attendance;

use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class CurrentDatetimeDisplayTest extends TestCase
{
    public function test_4_1_現在の日時情報が_UIと同じ形式で出力されている()
    {
        /** @var \App\Models\User $user */
        $user = User::where('email', 'user@example.com')->first();
        $user->email_verified_at = now();
        $user->save();

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $now = Carbon::now()->locale('ja');
        $expectedDate = $now->isoFormat('YYYY年M月D日(ddd)'); // 例: 2025年6月29日(日)
        $expectedTime = $now->format('H:i'); // 例: 09:01

        $response->assertSee($expectedDate);
        $response->assertSee($expectedTime);
    }
}
