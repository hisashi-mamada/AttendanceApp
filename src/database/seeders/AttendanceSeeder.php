<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('role', 'user')->get();
        $start = Carbon::parse('2025-06-20');
        $end = Carbon::parse('2025-07-03');

        foreach ($users as $user) {
            for ($date = $start->copy(); $date <= $end; $date->addDay()) {
                Attendance::updateOrCreate(
                    ['user_id' => $user->id, 'date' => $date],
                    [
                        'clock_in_time' => $date->copy()->setTime(9, 0, 0),
                        'clock_out_time' => $date->copy()->setTime(18, 0, 0),

                        'status' => 'worked',
                        'remarks' => '通常勤務',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}
