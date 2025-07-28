<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_勤怠詳細画面に表示されるデータが選択したものになっている()
    {
        // 日付設定
        $date = Carbon::today()->toDateString();

        // 管理者ユーザー作成
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        // 一般ユーザー作成
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        // 勤怠データ作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $date,
            'clock_in_time' => "$date 08:00:00",
            'clock_out_time' => "$date 17:00:00",
            'remarks' => '体調良好',
        ]);

        // 休憩データ作成
        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start_time' => "$date 12:00:00",
            'break_end_time' => "$date 13:00:00",
        ]);

        // 管理者として勤怠詳細画面にアクセス
        $response = $this->actingAs($admin)->get(route('admin.attendances.show', ['id' => $attendance->id]));

        // 表示されるべきデータを確認
        $response->assertStatus(200);
        $response->assertSee($user->name);       // ユーザー名
        $response->assertSee('08:00');           // 出勤時間
        $response->assertSee('17:00');           // 退勤時間
        $response->assertSee('12:00');           // 休憩開始
        $response->assertSee('13:00');           // 休憩終了
        $response->assertSee('体調良好');        // 備考
    }

    public function test_出勤時間が退勤時間より後になっている場合_エラーメッセージが表示される()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $admin->id,
            'date' => Carbon::today()->toDateString(),
            'clock_in_time' => Carbon::today()->copy()->setTime(9, 0),
            'clock_out_time' => Carbon::today()->copy()->setTime(17, 0),
            'status' => '勤務中',
            'remarks' => 'テスト用'
        ]);

        $response = $this->actingAs($admin)
            ->patch(route('admin.attendances.update', ['id' => $attendance->id]), [
                'requested_clock_in_time' => '18:00',
                'requested_clock_out_time' => '09:00',
                'reason' => '出勤が退勤より遅いテスト',
                'breaks' => [
                    ['start' => '12:00', 'end' => '13:00']
                ]
            ]);

        $response->assertSessionHasErrors(['requested_clock_in_time']);
    }

    public function test_休憩開始時間が退勤時間より後になっている場合_エラーメッセージが表示される()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $admin->id,
            'date' => Carbon::today()->toDateString(),
            'clock_in_time' => Carbon::today()->copy()->setTime(9, 0),
            'clock_out_time' => Carbon::today()->copy()->setTime(17, 0),
            'status' => '勤務中',
            'remarks' => 'テスト用'
        ]);


        $response = $this->actingAs($admin)
            ->patch(route('admin.attendances.update', ['id' => $attendance->id]), [
                'requested_clock_in_time' => '10:00',
                'requested_clock_out_time' => '17:00',
                'reason' => '休憩が退勤より後のテスト',
                'breaks' => [
                    ['start' => '18:00', 'end' => '19:00']
                ]
            ]);


        $response->assertSessionHasErrors([
            'breaks.0.start' => '休憩時間が不適切な値です',
        ]);
    }

    public function test_休憩終了時間が退勤時間より後になっている場合_エラーメッセージが表示される()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $admin->id,
            'date' => Carbon::today()->toDateString(),
            'clock_in_time' => Carbon::today()->copy()->setTime(9, 0),
            'clock_out_time' => Carbon::today()->copy()->setTime(17, 0),
            'status' => '勤務中',
            'remarks' => '休憩終了時間のバリデーションテスト',
        ]);

        $response = $this->actingAs($admin)
            ->patch(route('admin.attendances.update', ['id' => $attendance->id]), [
                'requested_clock_in_time'  => '09:00',
                'requested_clock_out_time' => '17:00',
                'remarks'        => '休憩終了が退勤より遅い',
                'breaks' => [
                    [
                        'start' => '16:00',
                        'end'   => '18:00', // ← 退勤時間より遅い
                    ]
                ]
            ]);

        $response->assertSessionHasErrors([
            'breaks.0.end' => '休憩時間が不適切な値です', // ← 実装に合わせて
        ]);
    }

    public function test_備考欄が未入力の場合_エラーメッセージが表示される(): void
    {
        // 1. 管理者ユーザー
        $admin = User::factory()->create(['role' => 'admin']);

        // 2. ひな形となる勤怠レコード
        $attendance = Attendance::factory()->create([
            'user_id'        => $admin->id,
            'date'           => Carbon::today()->toDateString(),
            'clock_in_time'  => Carbon::today()->copy()->setTime(9, 0),
            'clock_out_time' => Carbon::today()->copy()->setTime(17, 0),
            'status'         => '勤務中',
            'remarks'        => '初期備考',
        ]);

        // 3. 備考（reason）を未入力で PATCH 要求
        $response = $this->actingAs($admin)->patch(
            route('admin.attendances.update', ['id' => $attendance->id]),
            [
                // 勤怠必須項目
                'requested_clock_in_time'  => '09:00',
                'requested_clock_out_time' => '17:00',

                // 休憩 (正常値)
                'breaks' => [
                    ['start' => '12:00', 'end' => '13:00'],
                ],

                // ★ 備考欄を空にして送信
                'reason' => '',   // ← rules() で required の対象
            ]
        );

        // 4. バリデーションメッセージを確認
        $response->assertSessionHasErrors([
            'reason' => '備考を記入してください',
        ]);
    }
}
