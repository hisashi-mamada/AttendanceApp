<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceClockInTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_勤務外ユーザーが出勤ボタンを押すと勤務中に切り替わる()
    {
        // Carbonの固定（例：2025-07-21 09:00）
        $fixedNow = Carbon::create(2025, 7, 21, 9, 0);
        Carbon::setTestNow($fixedNow);

        // ユーザー作成 & ログイン
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user);

        // 出勤前にボタンが表示されていることを確認
        $response = $this->get('/attendance');
        $response->assertSee('出勤');

        // 出勤処理を実行
        $postResponse = $this->post('/attendance', [
            'action' => 'clock_in',
        ]);

        // リダイレクト後に「勤務中」と表示されているか確認
        $responseAfter = $this->get('/attendance');
        $responseAfter->assertSee('出勤中');

        // DB上に正しい出勤レコードがあるか確認
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'clock_in_time' => $fixedNow->toDateTimeString(),
        ]);
    }

    /** @test */
    public function test_出勤は一日一回のみできる()
    {
        Carbon::setTestNow(Carbon::create(2025, 7, 21, 9, 0));

        // ユーザー作成
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user);

        // 出勤処理（1回目）
        $this->post('/attendance', [
            'action' => 'clock_in'
        ]);

        // 出勤処理（2回目、無効なはず）
        $response = $this->post('/attendance', [
            'action' => 'clock_in'
        ]);

        // 勤怠レコードは1件のみ
        $this->assertEquals(1, Attendance::where('user_id', $user->id)->count());
    }

    /** @test */
    public function test_出勤時刻が勤怠一覧画面で確認できる()
    {
        // 1. 日本時間の9:00を明示的にセット
        $fixedNow = Carbon::createFromFormat('Y-m-d H:i:s', '2025-07-21 09:00:00', 'Asia/Tokyo');
        Carbon::setTestNow($fixedNow);

        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user);

        // 2. 出勤処理
        $this->post('/attendance', [
            'action' => 'clock_in',
        ]);

        // 3. データベースに保存されたか確認
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'date' => $fixedNow->copy()->startOfDay()->toDateTimeString(),  // "2025-07-21 00:00:00"
        ]);


        // 4. 一覧画面にアクセス
        $response = $this->get('/attendance/list');
        $response->assertStatus(200);

        // 5. 出勤時刻が表示されているか確認
        $expectedTime = $fixedNow->format('H:i'); // → "09:00"
        $response->assertSee($expectedTime);
    }
}
