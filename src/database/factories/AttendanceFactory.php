<?php

namespace Database\Factories;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition()
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'date' => now(),
            'clock_in_time' => now(),
            'clock_out_time' => now()->addHours(8),
            'status' => '勤務中',
        ];
    }
}
