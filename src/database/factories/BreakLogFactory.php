<?php

namespace Database\Factories;

use App\Models\BreakLog;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;

class BreakLogFactory extends Factory
{
    protected $model = BreakLog::class;

    public function definition(): array
    {
        return [
            'attendance_id' => Attendance::factory(), // 必要なら明示的に指定
            'break_start'   => $this->faker->time('H:i:s'),
            'break_end'     => $this->faker->time('H:i:s'),
        ];
    }
}
