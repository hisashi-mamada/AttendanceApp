<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_勤務外の場合_ステータスが勤務外と表示される()
    {
        $user = User::factory()->create([
            'role' => 'user'
        ]);
        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSeeText('勤務外');
    }

    /** @test */
    public function test_出勤中の場合_ステータスが出勤中と表示される()
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);
        $this->actingAs($user);

        $today = now()->startOfDay();

        // 勤怠レコードを出勤中状態で作成（clock_in_timeのみ）
        \App\Models\Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'clock_in_time' => now(),
            'clock_out_time' => null,
            'status' => '出勤中',
        ]);

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSeeText('出勤中');
    }

    /** @test */
    public function test_休憩中の場合_ステータスが休憩中と表示される()
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);
        $this->actingAs($user);

        $today = now()->startOfDay();

        // 出勤中の状態を作成
        $attendance = \App\Models\Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'clock_in_time' => now(),
            'clock_out_time' => null,
            'status' => '出勤中',
        ]);

        // break_start_time のみ（＝休憩中）
        \App\Models\BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start_time' => now(),
            'break_end_time' => null,
        ]);

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSeeText('休憩中');
    }

    /** @test */
    public function test_退勤済の場合_ステータスが退勤済と表示される()
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);
        $this->actingAs($user);

        $today = now()->startOfDay();

        \App\Models\Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'clock_in_time' => now()->subHours(8),
            'clock_out_time' => now(),
            'status' => '退勤済',
        ]);

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSeeText('退勤済');
    }
}
