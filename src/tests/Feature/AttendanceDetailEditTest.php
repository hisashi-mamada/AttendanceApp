<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrectionRequest;
use Carbon\Carbon;

class AttendanceDetailEditTest extends TestCase
{
    use RefreshDatabase;
    /** @test */
    public function test_出勤時間が退勤時間より後の場合_エラーメッセージが表示される()
    {
        // ユーザーと勤怠データを作成
        $user = User::factory()->create(['role' => 'user']);
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        // ログイン
        $this->actingAs($user);

        // 不正な時間で更新
        $response = $this->patch(route('attendance.update', ['id' => $attendance->id]), [
            'requested_clock_in_time'  => '19:00',
            'requested_clock_out_time' => '18:00', // ← 出勤 > 退勤でNG
            'reason'                   => '不正なテスト',
        ]);



        // バリデーションエラーの検証
        $response->assertSessionHasErrors([
            'requested_clock_in_time' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_休憩開始時間が退勤時間より後の場合_エラーメッセージが表示される(): void
    {
        // ユーザー・勤怠データの作成
        $user = User::factory()->create(['role' => 'user']);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in_time' => '09:00:00',
            'clock_out_time' => '18:00:00',
        ]);

        // ログイン状態でPOST送信
        $response = $this->actingAs($user)->patch(route('attendance.update', ['id' => $attendance->id]), [
            'requested_clock_in_time' => '09:00',
            'requested_clock_out_time' => '18:00',
            'reason' => '休憩が不適切な時間テスト',
            'breaks' => [
                ['start' => '18:30', 'end' => '19:00'], // NG：startが退勤時間より後
            ],
        ]);

        // バリデーションエラーチェック
        $response->assertSessionHasErrors([
            'breaks.0.start' => '休憩時間が不適切な値です',
        ]);
    }

    public function test_休憩終了時間が退勤時間より後の場合_エラーメッセージが表示される(): void
    {
        // ユーザー・勤怠データの作成
        $user = User::factory()->create(['role' => 'user']);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in_time' => '09:00:00',
            'clock_out_time' => '18:00:00',
        ]);

        // ログイン状態でPATCH送信
        $response = $this->actingAs($user)->patch(route('attendance.update', ['id' => $attendance->id]), [
            'requested_clock_in_time' => '09:00',
            'requested_clock_out_time' => '18:00',
            'reason' => '休憩終了が退勤後',
            'breaks' => [
                ['start' => '17:00', 'end' => '18:30'], // ← NG：endが退勤より後
            ],
        ]);

        // バリデーションエラーの検証
        $response->assertSessionHasErrors([
            'breaks.0.end' => '休憩時間が不適切な値です',
        ]);
    }

    public function test_備考欄が未入力の場合_エラーメッセージが表示される(): void
    {
        // ユーザー・勤怠データ作成
        $user = User::factory()->create(['role' => 'user']);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in_time' => '09:00:00',
            'clock_out_time' => '18:00:00',
        ]);

        // ログイン状態でPATCH送信（reason未入力）
        $response = $this->actingAs($user)->patch(route('attendance.update', ['id' => $attendance->id]), [
            'requested_clock_in_time' => '09:00',
            'requested_clock_out_time' => '18:00',
            'reason' => '', // ← 未入力
            'breaks' => [
                ['start' => '12:00', 'end' => '13:00'], // 適切な休憩
            ],
        ]);

        // バリデーションエラーの検証
        $response->assertSessionHasErrors([
            'reason' => '備考を記入してください',
        ]);
    }

    public function test_修正申請処理が実行される()
    {
        $user = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)->patch(route('attendance.update', ['id' => $attendance->id]), [
            'requested_clock_in_time' => '09:00',
            'requested_clock_out_time' => '18:00',
            'reason' => '体調不良のため遅刻しました',
            'breaks' => [
                ['start' => '12:00', 'end' => '13:00'],
            ],
        ]);


        $response->assertRedirect(route('attendance.detail', ['id' => $attendance->id]));

        $this->assertDatabaseHas('attendance_correction_requests', [
            'attendance_id' => $attendance->id,
            'reason' => '体調不良のため遅刻しました',
            'status' => 'pending',
        ]);

        // 管理者の確認
        $adminResponse = $this->actingAs($admin)->get(route('admin.requests.index', ['tab' => 'pending']));

        $adminResponse->assertStatus(200);
        $adminResponse->assertSee('体調不良のため遅刻しました');
        $adminResponse->assertSee('承認待ち');
    }

    public function test_ログインユーザーの承認待ち申請が一覧表示される()
    {
        // 一般ユーザーを作成
        $user = User::factory()->create(['role' => 'user']);

        $date = Carbon::today();

        $attendances = [];

        for ($i = 0; $i < 3; $i++) {
            $date = Carbon::today()->addDays($i);

            $attendance = Attendance::create([
                'user_id' => $user->id,
                'date' => $date,
                'clock_in_time' => $date->copy()->setTime(9, 0),
                'clock_out_time' => $date->copy()->setTime(18, 0),
                'status' => '勤務中',
            ]);

            $attendances[] = $attendance;
        }

        // 各勤怠情報に対して修正申請
        foreach ($attendances as $index => $attendance) {
            $this->actingAs($user)->patch(route('attendance.update', ['id' => $attendance->id]), [
                'requested_clock_in_time' => '09:00',
                'requested_clock_out_time' => '18:00',
                'reason' => "申請理由{$index}",
                'breaks' => [
                    ['start' => '12:00', 'end' => '13:00'],
                ],
            ]);
        }

        // 一般ユーザーとして申請一覧画面にアクセス
        $response = $this->actingAs($user)->get(route('request.list', ['tab' => 'pending']));

        $response->assertStatus(200);
    }

    public function test_ログインユーザーの承認済み申請が一覧表示される()
    {
        // 一般ユーザーと管理者ユーザーを作成
        $user = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);

        $attendances = [];

        for ($i = 0; $i < 3; $i++) {
            $date = Carbon::today()->addDays($i);

            // 勤怠情報作成
            $attendance = Attendance::create([
                'user_id' => $user->id,
                'date' => $date,
                'clock_in_time' => $date->copy()->setTime(9, 0),
                'clock_out_time' => $date->copy()->setTime(18, 0),
                'status' => '勤務中',
            ]);

            // 修正申請作成（初期状態は pending）
            AttendanceCorrectionRequest::create([
                'user_id' => $user->id,
                'attendance_id' => $attendance->id,
                'requested_clock_in_time' => '09:00:00',
                'requested_clock_out_time' => '18:00:00',
                'reason' => "テスト承認申請{$i}",
                'status' => 'pending',
            ]);

            $attendances[] = $attendance;
        }

        // 管理者による承認処理（status を approved に変更）
        foreach ($attendances as $attendance) {
            $correctionRequest = $attendance->correctionRequest;

            if (!$correctionRequest) {
                $this->fail("correctionRequest が null です。attendance_id: {$attendance->id}");
            }

            $correctionRequest->status = 'approved';
            $correctionRequest->approved_by = $admin->id;
            $correctionRequest->save();
        }

        // 一般ユーザーとして承認済みタブにアクセス
        $response = $this->actingAs($user)->get(route('request.list', ['tab' => 'approved']));
        $response->assertStatus(200);

        // 申請理由と「承認済み」の表示確認
        foreach ($attendances as $i => $attendance) {
            $response->assertSee("テスト承認申請{$i}");
            $response->assertSee('承認済み');
        }
    }

    public function test_申請詳細リンクから申請詳細画面に遷移できる(): void
    {
        // 勤怠情報を持つユーザーを作成
        $user = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2023-06-01',
        ]);

        // ログインして修正申請を実行
        $this->actingAs($user)->patch(route('attendance.update', ['id' => $attendance->id]), [
            'requested_clock_in_time' => '09:00',
            'requested_clock_out_time' => '18:00',
            'reason' => 'テスト理由',
            'breaks' => [
                ['start' => '12:00', 'end' => '13:00'],
            ],
        ]);


        // 修正申請が登録されていることを確認しつつ取得
        $correctionRequest = AttendanceCorrectionRequest::where('attendance_id', $attendance->id)->latest()->first();
        $this->assertNotNull($correctionRequest, '修正申請が登録されていません');

        // 「詳細」リンク先にアクセス
        $response = $this->actingAs($user)->get(route('request.detail', ['id' => $correctionRequest->id]));

        // 正しく遷移できたか検証
        $response->assertStatus(200);
    }
}
