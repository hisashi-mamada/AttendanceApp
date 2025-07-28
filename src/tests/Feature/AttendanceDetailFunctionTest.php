<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;

class AttendanceDetailFunctionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 勤怠詳細画面にユーザーの名前が表示される()
    {
        // 1. ユーザー作成 & ログイン
        $user = \App\Models\User::factory()->create(['name' => '山田 太郎', 'role' => 'user',]);
        $this->actingAs($user);

        // 2. 勤怠情報作成
        $attendance = \App\Models\Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now(),
        ]);

        // 3. 詳細ページへアクセス
        $response = $this->get(route('attendance.detail', ['id' => $attendance->id]));

        // 4. ステータス & 表示確認
        $response->assertStatus(200);
        $response->assertSee($user->name);
    }

    public function test_勤怠詳細画面に選択した日付が表示される(): void
    {
        // テストデータ準備
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user);

        $attendanceDate = Carbon::create(2025, 7, 15);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $attendanceDate,
        ]);

        // 詳細ページにアクセス
        $response = $this->get(route('attendance.detail', ['id' => $attendance->id]));

        // 日付の表示形式に合わせて確認
        $response->assertStatus(200);
        $response->assertSee($attendanceDate->format('Y年'));
        $response->assertSee($attendanceDate->format('n月j日'));
    }

    public function test_勤怠詳細画面に出勤退勤時刻が表示される(): void
    {
        // 1. ユーザーを作成してログイン
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user);

        // 2. 出勤・退勤時刻を設定
        $date = Carbon::create(2025, 7, 15);
        $clockIn  = Carbon::create(2025, 7, 15, 9, 0, 0);
        $clockOut = Carbon::create(2025, 7, 15, 18, 0, 0);

        $attendance = Attendance::factory()->create([
            'user_id'        => $user->id,
            'date'           => $date,
            'clock_in_time'  => $clockIn,
            'clock_out_time' => $clockOut,
        ]);

        // 3. 詳細ページにアクセス
        $response = $this->get(route('attendance.detail', ['id' => $attendance->id]));

        // 4. 出勤・退勤時刻が表示されていることを確認（表示形式に合わせて）
        $response->assertStatus(200);
        $response->assertSee($clockIn->format('H:i'));
        $response->assertSee($clockOut->format('H:i'));
    }

    public function test_勤怠詳細画面に休憩時間が表示される(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::create(2025, 7, 15),
        ]);

        // 休憩データを複数登録しても良い（とりあえず1件）
        $breakStart = Carbon::create(2025, 7, 15, 12, 0, 0);
        $breakEnd   = Carbon::create(2025, 7, 15, 13, 0, 0);

        \App\Models\BreakTime::factory()->create([
            'attendance_id'     => $attendance->id,
            'break_start_time'  => $breakStart,
            'break_end_time'    => $breakEnd,
        ]);

        $response = $this->get(route('attendance.detail', ['id' => $attendance->id]));

        $response->assertStatus(200);
        $response->assertSee($breakStart->format('H:i'));
        $response->assertSee($breakEnd->format('H:i'));
    }
}
