<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\BreakTime;

class BreakSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attendances = Attendance::all();

        foreach ($attendances as $attendance) {
            BreakTime::create([
                'attendance_id' => $attendance->id,
                'break_start_time' => $attendance->date . ' 12:00:00',
                'break_end_time' => $attendance->date . ' 13:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
