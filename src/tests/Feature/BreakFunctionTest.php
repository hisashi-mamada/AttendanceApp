<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class BreakFunctionTest extends TestCase
{

    use RefreshDatabase;

    public function test_休憩ボタンが正しく機能する()
    {
        // 1. 時刻固定
        $fixedNow = Carbon::create(2025, 7, 21, 10, 0, 0);
        Carbon::setTestNow($fixedNow);

        // 2. 出勤済みの状態を用意
        $user = User::factory()->create(['role' => 'user']);

        Attendance::create([
            'user_id' => $user->id,
            'date' => $fixedNow->copy()->startOfDay(),
            'clock_in_time' => $fixedNow->copy()->subHour(), // 9:00出勤済み
            'status' => '未設定',
        ]);

        $this->actingAs($user);

        // 3. 勤怠画面にアクセスして「休憩入」ボタンを確認
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('休憩入');

        // 4. 「休憩入」ボタンのPOST処理を実行
        $this->post('/attendance', [
            'action' => 'break_in',
        ]);

        // 5. 再度アクセスして「休憩中」の表示を確認
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    public function test_休憩は一日に何回でもできる()
    {
        // 1. 時刻固定
        $fixedNow = Carbon::create(2025, 7, 21, 10, 0, 0);
        Carbon::setTestNow($fixedNow);

        // 2. 出勤済みの状態を用意
        $user = User::factory()->create(['role' => 'user']);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $fixedNow->copy()->startOfDay(),
            'clock_in_time' => $fixedNow->copy()->subHour(), // 9:00出勤済み
            'status' => '未設定',
        ]);

        $this->actingAs($user);

        // 3. 最初の休憩入
        $this->post('/attendance', [
            'action' => 'break_in',
        ]);

        // 4. 最初の休憩戻
        $this->post('/attendance', [
            'action' => 'break_out',
        ]);

        // 5. 再度画面にアクセスして「休憩入」ボタンが表示されていることを確認
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('休憩入');
    }

    public function test_休憩戻ボタンが正しく機能する()
    {
        // 1. 時刻固定
        $fixedNow = Carbon::create(2025, 7, 21, 10, 0, 0);
        Carbon::setTestNow($fixedNow);

        // 2. 出勤中のユーザー作成
        $user = User::factory()->create(['role' => 'user']);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $fixedNow->copy()->startOfDay(),
            'clock_in_time' => $fixedNow->copy()->subHour(), // 9:00に出勤済み
            'status' => '未設定',
        ]);

        $this->actingAs($user);

        // 3. 休憩入
        $this->post('/attendance', [
            'action' => 'break_in',
        ]);

        // 4. 休憩戻
        $this->post('/attendance', [
            'action' => 'break_out',
        ]);

        // 5.ステータス確認
        $response = $this->get('/attendance');
        $response->assertStatus(200);
    }

    public function test_休憩戻は一日に何回でもできる()
    {
        $fixedNow = Carbon::create(2025, 7, 21, 10, 0, 0);
        Carbon::setTestNow($fixedNow);

        $user = User::factory()->create(['role' => 'user']);

        // 勤怠レコード作成（出勤済みの状態）
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $fixedNow->copy()->startOfDay(),
            'clock_in_time' => $fixedNow->copy()->subHour(), // 9:00 出勤
            'status' => '未設定',
        ]);

        $this->actingAs($user);

        // 1回目の休憩入
        $this->post('/attendance', [
            'action' => 'break_in',
        ]);

        // 1回目の休憩戻
        $this->post('/attendance', [
            'action' => 'break_out',
        ]);

        // 2回目の休憩入
        $this->post('/attendance', [
            'action' => 'break_in',
        ]);

        // 勤務画面にアクセス → 「休憩戻」ボタンがあることを確認
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('休憩戻');
    }

    public function test_休憩時刻が勤怠一覧画面で確認できる()
    {
        $fixedNow = Carbon::create(2025, 7, 21, 10, 0, 0);
        Carbon::setTestNow($fixedNow);

        $user = User::factory()->create(['role' => 'user']);

        // 出勤済みの勤怠レコードを作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $fixedNow->copy()->startOfDay(),
            'clock_in_time' => $fixedNow->copy()->subHour(), // 9:00
            'status' => '未設定',
        ]);

        $this->actingAs($user);

        // 休憩入（10:00）
        $this->post('/attendance', ['action' => 'break_in']);

        // 休憩戻（10:30）
        Carbon::setTestNow($fixedNow->copy()->addMinutes(30)); // 10:30
        $this->post('/attendance', ['action' => 'break_out']);

        // 勤怠一覧画面にアクセス
        $response = $this->get('/attendance/list');
        $response->assertStatus(200);

        // 合計休憩時間が「0:30」と表示されているか確認
        $response->assertSee('0:30');
    }
}
