<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        return [
            'user_id'     => User::factory(), // ユーザーも同時に作成
            'start_time'  => $this->faker->dateTimeBetween('-1 week', 'now'),
            'end_time'    => $this->faker->optional()->dateTimeBetween('now', '+1 day'),
            'break_start' => $this->faker->optional()->dateTimeBetween('now', '+1 hour'),
            'break_end'   => $this->faker->optional()->dateTimeBetween('now', '+2 hour'),
            'memo'        => $this->faker->optional()->sentence(),
        ];
    }
}
