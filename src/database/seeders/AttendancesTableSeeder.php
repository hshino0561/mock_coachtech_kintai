<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendancesTableSeeder extends Seeder
{
    public function run(): void
    {
        // 共通値
        $now = Carbon::now();
        $userId = 1;
        $date = Carbon::parse('2025-06-21');

        // ① attendances
        $attendanceId = DB::table('attendances')->insertGetId([
            'user_id'    => $userId,
            'date'       => $date->format('Y-m-d'),
            'start_time' => '09:00:00',
            'end_time'   => '18:00:00',
            'memo'       => '出勤済み',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // ② stamp_correction_requests
        $stampRequestId = DB::table('stamp_correction_requests')->insertGetId([
            'attendance_id'    => $attendanceId,
            'user_id'          => $userId,
            'attendance_date'  => $date->format('Y-m-d'),
            'start_time'       => '09:15:00',
            'end_time'         => '18:00:00',
            'memo'             => '少し遅れたため修正依頼',
            'status'           => 'pending',
            'created_at'       => $now,
            'updated_at'       => $now,
        ]);

        // ③ break_logs
        DB::table('break_logs')->insert([
            [
                'attendance_id' => $attendanceId,
                'user_id'       => $userId,
                'break_date'    => $date->format('Y-m-d'),
                'break_start'   => '12:00:00',
                'break_end'     => '12:45:00',
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'attendance_id' => $attendanceId,
                'user_id'       => $userId,
                'break_date'    => $date->format('Y-m-d'),
                'break_start'   => '15:30:00',
                'break_end'     => '15:45:00',
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
        ]);

        // ④ break_correction_requests
        DB::table('break_correction_requests')->insert([
            [
                'stamp_correction_request_id' => $stampRequestId,
                'break_start'   => '12:10:00',
                'break_end'     => '12:50:00',
                'created_at'    => $now,
                'updated_at'    => $now,
            ]
        ]);
    }
}
