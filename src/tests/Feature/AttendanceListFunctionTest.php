<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceListFunctionTest extends TestCase
{
    use RefreshDatabase;

    public function test_自分が行った勤怠情報が全て表示されている()
    {
        // 1. テスト固定日を設定
        $fixedNow = Carbon::create(2025, 7, 1, 9, 0, 0);
        Carbon::setTestNow($fixedNow);

        // 2. ユーザーと勤怠データを3件作成
        $user = User::factory()->create(['role' => 'user']);
        for ($i = 0; $i < 3; $i++) {
            Attendance::factory()->create([
                'user_id' => $user->id,
                'clock_in_time' => $fixedNow->copy()->addDays($i)->setTime(9, 0),
                'clock_out_time' => $fixedNow->copy()->addDays($i)->setTime(18, 0),
                'date' => $fixedNow->copy()->addDays($i),
            ]);
        }

        // 他ユーザーの勤怠も作成（表示されてはいけない）
        $otherUser = User::factory()->create(['role' => 'user']);
        Attendance::factory()->create([
            'user_id' => $otherUser->id,
            'clock_in_time' => $fixedNow->copy()->setTime(10, 0),
            'clock_out_time' => $fixedNow->copy()->setTime(19, 0),
            'date' => $fixedNow->copy()->startOfMonth(),
        ]);

        // 3. ログインして一覧ページにアクセス
        $this->actingAs($user);
        $response = $this->get('/attendance/list');
        $response->assertStatus(200);

        // 4. 自分の勤怠情報（3件分）が表示されていることを確認
        $attendances = Attendance::where('user_id', $user->id)->get();
        foreach ($attendances as $attendance) {
            $response->assertSee(\Carbon\Carbon::parse($attendance->date)->format('m/d'));
            $response->assertSee(\Carbon\Carbon::parse($attendance->clock_in_time)->format('H:i'));
            $response->assertSee(\Carbon\Carbon::parse($attendance->clock_out_time)->format('H:i'));
        }

        // 5. 他人の勤怠情報が表示されていないことを確認
        $response->assertDontSee('10:00');
        $response->assertDontSee('19:00');
    }

    /** @test */
    public function 勤怠一覧画面に遷移した際に現在の月が表示される()
    {
        // 1. ユーザー作成 & ログイン
        $user = User::factory()->create([
            'role' => 'user'
        ]);
        $this->actingAs($user);

        // 2. 一覧ページにアクセス
        $response = $this->get('/attendance/list');

        // 3. 現在の年月を確認
        $currentMonth = now()->format('Y/m');
        $response->assertStatus(200);
        $response->assertSee($currentMonth);
    }

    public function test_前月ボタンを押した時に前月の情報が表示される()
    {
        // 1. ユーザー作成（role必須）
        $user = User::factory()->create(['role' => 'user']);

        // 2. 勤怠データを「前月の日付」で作成（例: 今日が8月 → 7月1日分）
        $lastMonthDate = Carbon::now()->subMonth()->startOfMonth();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $lastMonthDate,
            'clock_in_time' => Carbon::parse($lastMonthDate)->setTime(9, 0),
            'clock_out_time' => Carbon::parse($lastMonthDate)->setTime(18, 0),
            'status' => '勤務中',
        ]);

        // 3. ユーザーとしてログインし、「前月リンク」を含むページを訪問
        $this->actingAs($user);

        // 4. 「前月」リンクを生成
        $prevMonth = Carbon::now()->subMonth()->format('Y-m');

        // 5. 前月表示用のURLにアクセス
        $response = $this->get(route('attendances.list', ['month' => $prevMonth]));

        // 6. 日付や時間が表示されていることを確認
        $response->assertStatus(200);
        $response->assertSee(Carbon::parse($lastMonthDate)->isoFormat('MM/DD(ddd)'));
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_翌月ボタンを押した時に翌月の情報が表示される()
    {
        // 1. ログインユーザー作成
        $user = User::factory()->create(['role' => 'user']);

        // 2. 翌月の勤怠データを作成（例: 今が8月 → 9月1日）
        $nextMonthDate = Carbon::now()->addMonth()->startOfMonth();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $nextMonthDate,
            'clock_in_time' => Carbon::parse($nextMonthDate)->setTime(9, 0),
            'clock_out_time' => Carbon::parse($nextMonthDate)->setTime(18, 0),
            'status' => '勤務中',
        ]);

        // 3. ユーザーとしてログイン
        $this->actingAs($user);

        // 4. 翌月のURLを生成してアクセス
        $nextMonth = Carbon::now()->addMonth()->format('Y-m');
        $response = $this->get(route('attendances.list', ['month' => $nextMonth]));

        // 5. 表示されていることを確認
        $response->assertStatus(200);
        $response->assertSee(Carbon::parse($nextMonthDate)->isoFormat('MM/DD(ddd)'));
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_詳細ボタンを押すと勤怠詳細画面に遷移する()
    {
        // 1. ユーザー作成
        $user = User::factory()->create(['role' => 'user']);

        // 2. 勤怠データ作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->toDateString(),
            'clock_in_time' => Carbon::now()->setTime(9, 0),
            'clock_out_time' => Carbon::now()->setTime(18, 0),
            'status' => '勤務中',
        ]);

        // 3. ログイン
        $this->actingAs($user);

        // 4. 「詳細」リンク先にアクセス（GET）
        $response = $this->get('/attendance/detail/' . $attendance->id);

        // 5. ステータスと表示確認
        $response->assertStatus(200);
        $response->assertSee('勤怠詳細'); // ← 詳細画面に表示されているテキストなど
        $response->assertSee(Carbon::parse($attendance->date)->format('Y年'));
        $response->assertSee(Carbon::parse($attendance->date)->format('n月j日'));
    }
}
