<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AdminAttendanceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function その日になされた全ユーザーの勤怠情報が正確に確認できる()
    {
        // 今日の日付を取得
        $date = Carbon::today()->toDateString();

        // 管理者ユーザーを作成
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        // 勤怠データを作成
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $date,
            'clock_in_time' => "$date 09:00:00",
            'clock_out_time' => "$date 18:00:00",
        ]);

        // 休憩時間を1時間に設定
        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start_time' => "$date 12:00:00",
            'break_end_time' => "$date 13:00:00",
        ]);

        // 管理者としてアクセス
        $response = $this->actingAs($admin)->get(route('admin.attendances.index', ['date' => $date]));

        // アサーション：勤怠情報が表示されているか
        $response->assertStatus(200);
        $response->assertSee($user->name);        // ユーザー名
        $response->assertSee('09:00');            // 出勤時間
        $response->assertSee('18:00');            // 退勤時間
        $response->assertSee('1:00');             // 休憩時間
        $response->assertSee('8:00');             // 実労働時間（9時間 - 1時間）
    }

    public function test_遷移した際に現在の日付が表示される()
    {
        // 今日の日付
        $today = Carbon::today()->format('Y年n月j日');

        // 管理者ユーザーを作成
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);


        // 管理者としてアクセス
        $response = $this->actingAs($admin)->get(route('admin.attendances.index'));

        // ステータスコード確認
        $response->assertStatus(200);

        // 今日の日付が表示されていることを確認
        $response->assertSee($today);
    }

    public function test_前日を押下した時に前の日の勤怠情報が表示される()
    {
        // 日付設定
        $today = Carbon::today();
        $yesterday = $today->copy()->subDay();

        // 管理者ユーザーを作成
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        // 勤怠データ作成（前日分）
        $user = User::factory()->create([
            'role' => 'user',
        ]);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $yesterday->toDateString(),
            'clock_in_time' => $yesterday->toDateString() . ' 08:00:00',
            'clock_out_time' => $yesterday->toDateString() . ' 17:00:00',
        ]);

        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start_time' => $yesterday->toDateString() . ' 12:00:00',
            'break_end_time' => $yesterday->toDateString() . ' 13:00:00',
        ]);

        // 「前日」ボタン押下と同じ動作（URLに ?date=前日 をつける）
        $response = $this->actingAs($admin)->get(route('admin.attendances.index', [
            'date' => $yesterday->toDateString()
        ]));

        // 前日の日付が表示されていることを確認（bladeのフォーマットと一致させる）
        $response->assertStatus(200);
        $response->assertSee($yesterday->format('Y年n月j日'));  // タイトル部分
        $response->assertSee($user->name);                     // ユーザー名
        $response->assertSee('08:00');                         // 出勤時間
        $response->assertSee('17:00');                         // 退勤時間
        $response->assertSee('1:00');                          // 休憩
        $response->assertSee('8:00');                          // 実労働時間
    }

    public function test_翌日を押下した時に次の日の勤怠情報が表示される()
    {
        $today = Carbon::today();
        $tomorrow = $today->copy()->addDay();

        // 管理者ユーザー
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        // 翌日の勤怠データを作成
        $user = User::factory()->create([
            'role' => 'user',
        ]);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $tomorrow->toDateString(),
            'clock_in_time' => $tomorrow->format('Y-m-d') . ' 09:00:00',
            'clock_out_time' => $tomorrow->format('Y-m-d') . ' 18:00:00',
        ]);

        // 休憩時間 1時間
        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start_time' => $tomorrow->format('Y-m-d') . ' 12:00:00',
            'break_end_time' => $tomorrow->format('Y-m-d') . ' 13:00:00',
        ]);

        // 「翌日」ボタンのリンク先にアクセス
        $response = $this->actingAs($admin)->get(route('admin.attendances.index', ['date' => $tomorrow->toDateString()]));

        // アサーション：翌日の日付と勤怠情報が表示されること
        $response->assertStatus(200);
        $response->assertSee($user->name);                          // ユーザー名
        $response->assertSee('09:00');                              // 出勤時間
        $response->assertSee('18:00');                              // 退勤時間
        $response->assertSee('1:00');                               // 休憩時間
        $response->assertSee('8:00');                               // 実労働時間
        $response->assertSee($tomorrow->format('Y年n月j日'));       // 翌日の日付表示（画面上部）
    }
}
