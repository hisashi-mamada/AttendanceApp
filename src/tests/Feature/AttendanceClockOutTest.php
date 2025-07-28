<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AttendanceClockOutTest extends TestCase
{
    use RefreshDatabase;

    public function test_退勤ボタンが正しく機能する()
    {
        // テスト用の固定時刻を設定（9:00に出勤、17:00に退勤）
        $clockInTime = Carbon::create(2025, 7, 21, 9, 0, 0);
        $clockOutTime = Carbon::create(2025, 7, 21, 17, 0, 0);
        Carbon::setTestNow($clockInTime);

        // ユーザー作成 & 出勤済みの勤怠レコードを作成
        $user = User::factory()->create(['role' => 'user']);
        Attendance::create([
            'user_id' => $user->id,
            'date' => $clockInTime->copy()->startOfDay(),
            'clock_in_time' => $clockInTime,
            'status' => '未設定',
        ]);

        // ログイン処理
        $this->actingAs($user);

        // 1. 勤怠画面にアクセスして「退勤」ボタンが表示されているか確認
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('退勤');

        // 2. 時刻を17:00にして退勤処理
        Carbon::setTestNow($clockOutTime);
        $this->post('/attendance', ['action' => 'clock_out']);

        // 3. 勤怠画面に再度アクセスして「退勤済」と表示されていることを確認
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }

    public function test_退勤時刻が勤怠一覧画面で確認できる()
    {
        // 1. 出勤と退勤の固定時刻を設定
        $clockInTime = Carbon::create(2025, 7, 21, 9, 0, 0);
        $clockOutTime = Carbon::create(2025, 7, 21, 17, 30, 0);
        Carbon::setTestNow($clockInTime);

        // 2. ユーザー作成
        $user = User::factory()->create(['role' => 'user']);

        // 3. 出勤処理
        $this->actingAs($user);
        $this->post('/attendance', ['action' => 'clock_in']);

        // 4. 退勤処理（時間を17:30に進める）
        Carbon::setTestNow($clockOutTime);
        $this->post('/attendance', ['action' => 'clock_out']);

        // 5. 勤怠一覧にアクセスして退勤時刻が表示されているか確認
        $response = $this->get('/attendance/list');
        $response->assertStatus(200);

        // 6. 表示確認（フォーマットは blade に合わせて H:i）
        $response->assertSee($clockOutTime->format('H:i')); // "17:30"
    }
}
