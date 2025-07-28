<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrectionRequest;

class AdminRequestFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 承認待ちの修正申請が全て表示されている()
    {
        // 管理者ユーザー
        $admin = User::factory()->create(['role' => 'admin']);

        // 一般ユーザー
        $users = User::factory()->count(2)->create(['role' => 'user']);

        // 各ユーザーに勤怠を作成
        foreach ($users as $user) {
            $attendance = Attendance::factory()->create(['user_id' => $user->id]);

            AttendanceCorrectionRequest::factory()->create([
                'user_id' => $user->id,
                'attendance_id' => $attendance->id,
                'reason' => 'テスト理由',
                'status' => 'pending',
                'created_at' => now()->subDay(), // 表示される申請日時
            ]);
        }

        // 管理者でアクセス
        $response = $this->actingAs($admin)->get('/admin/requests?tab=pending');

        $response->assertStatus(200);

        // 各申請者の名前・理由が表示されているか確認
        foreach ($users as $user) {
            $response->assertSee($user->name);
        }

        $response->assertSee('テスト理由');
        $response->assertSee('承認待ち'); // Bladeでstatusに応じて「承認待ち」と表示
    }

    /** @test */
    public function 承認済みの修正申請が全て表示されている()
    {
        // 管理者と一般ユーザーを作成
        $admin = User::factory()->create(['role' => 'admin']);
        $users = User::factory()->count(2)->create(['role' => 'user']);

        // 各ユーザーに勤怠と「承認済み」修正申請を作成
        foreach ($users as $user) {
            $attendance = \App\Models\Attendance::factory()->create(['user_id' => $user->id]);

            \App\Models\AttendanceCorrectionRequest::factory()->create([
                'user_id' => $user->id,
                'attendance_id' => $attendance->id,
                'reason' => '承認済みテスト理由',
                'status' => 'approved',
                'created_at' => now()->subDays(2),
            ]);
        }

        // 管理者でアクセス
        $response = $this->actingAs($admin)->get('/admin/requests?tab=approved');

        $response->assertStatus(200);

        // 各ユーザーの名前・理由が表示されているか
        foreach ($users as $user) {
            $response->assertSee($user->name);
        }

        $response->assertSee('承認済みテスト理由');
        $response->assertSee('承認済み');
    }

    /** @test */
    public function 修正申請の詳細内容が正しく表示されている()
    {
        // 管理者と一般ユーザーを作成
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        // 勤怠データを作成
        $attendance = \App\Models\Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in_time' => '09:00:00',
            'clock_out_time' => '18:00:00',
        ]);

        // 休憩1件
        \App\Models\BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start_time' => '12:00:00',
            'break_end_time' => '13:00:00',
        ]);

        // 修正申請データ（requested_clock_in_timeに任意日をセット）
        $requestedClockIn = now()->setTime(9, 0);
        $correction = \App\Models\AttendanceCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'reason' => '遅延のため',
            'requested_clock_in_time' => $requestedClockIn,
            'requested_clock_out_time' => now()->setTime(18, 0),
            'status' => 'pending',
        ]);

        // アクセス先
        $url = '/admin/requests/' . $correction->id;

        $response = $this->actingAs($admin)->get($url);

        $response->assertStatus(200);

        // 内容確認
        $response->assertSee($user->name);
        $response->assertSee($requestedClockIn->format('n月j日'));  // 例: 8月29日
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
        $response->assertSee('遅延のため');
    }

    /** @test */
    public function 修正申請の承認処理が正しく行われる()
    {
        // 管理者と一般ユーザーを作成
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        // 勤怠データを作成
        $attendance = \App\Models\Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in_time' => '09:00:00',
            'clock_out_time' => '18:00:00',
        ]);

        // 修正申請（未承認状態）
        $correction = \App\Models\AttendanceCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 'pending',
            'reason' => '承認テスト',
        ]);

        // PATCHリクエストで承認処理を実行
        $response = $this->actingAs($admin)->patch('/admin/requests/' . $correction->id . '/approve');

        // リダイレクトで成功（戻る動作）
        $response->assertRedirect();

        // データベースを再取得して状態確認
        $this->assertDatabaseHas('attendance_correction_requests', [
            'id' => $correction->id,
            'status' => 'approved',
            'approved_by' => $admin->id,
        ]);
    }
}
