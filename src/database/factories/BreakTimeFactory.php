<?php

namespace Database\Factories;

use App\Models\BreakTime;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class BreakTimeFactory extends Factory
{
    protected $model = BreakTime::class;

    public function definition(): array
    {
        $start = Carbon::create(2025, 7, 15, 12, 0, 0);
        return [
            'attendance_id'     => Attendance::factory(),
            'break_start_time'  => $start,
            'break_end_time'    => (clone $start)->addHour(),
        ];
    }
}
