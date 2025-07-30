<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AttendanceCorrectionRequest;
use App\Models\User;
use App\Models\Attendance;

class AttendanceCorrectionRequestFactory extends Factory
{
    protected $model = AttendanceCorrectionRequest::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'attendance_id' => Attendance::factory(),
            'reason' => $this->faker->sentence,
            'requested_clock_in_time' => '09:00:00',
            'requested_clock_out_time' => '18:00:00',
            'status' => 'pending',
        ];
    }
}
