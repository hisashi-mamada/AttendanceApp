<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class AdminUserFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 管理者が全一般ユーザーの氏名とメールアドレスを確認できる()
    {
        // 管理者ユーザーを作成
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        // 一般ユーザーを3人作成
        $generalUsers = User::factory()->count(3)->create([
            'role' => 'user',
        ]);

        // 管理者でログインし、スタッフ一覧ページへアクセス
        $response = $this->actingAs($admin)
            ->get('/admin/users');

        $response->assertStatus(200);

        // 一般ユーザーのみが表示されていることを確認
        foreach ($generalUsers as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
        }
    }

    /** @test */
    public function ユーザーの勤怠情報が正しく表示される()
    {
        // 管理者と一般ユーザー
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        // 勤怠データ
        $attendance = \App\Models\Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'clock_in_time' => '09:00:00',
            'clock_out_time' => '18:00:00',
        ]);

        // 関連する休憩データ
        \App\Models\BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start_time' => '12:00:00',
            'break_end_time' => '13:00:00',
        ]);

        // アクセスURL
        $url = '/admin/users/' . $user->id . '/attendances';

        // 管理者でアクセス
        $response = $this->actingAs($admin)->get($url);

        $response->assertStatus(200);

        // 表示されているべき内容を確認
        $response->assertSee($user->name . 'さんの勤怠');
        $response->assertSee(now()->format('m/d'));

        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('1:00'); // 休憩合計
        $response->assertSee('8:00'); // 勤務合計
    }

    /** @test */
    public function 「前月」を押下した時に表示月の前月の情報が表示される()
    {
        // 管理者と一般ユーザーを作成
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        // 前月の任意日を生成（例：先月10日）
        $targetDate = now()->subMonth()->startOfMonth()->addDays(9); // 前月10日
        $targetMonth = $targetDate->format('Y-m');

        // 勤怠データを作成
        $attendance = \App\Models\Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $targetDate->format('Y-m-d'),
            'clock_in_time' => '10:00:00',
            'clock_out_time' => '19:00:00',
        ]);

        // 休憩データも追加（1時間）
        \App\Models\BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start_time' => '13:00:00',
            'break_end_time' => '14:00:00',
        ]);

        // 前月指定でアクセス
        $url = '/admin/users/' . $user->id . '/attendances?month=' . $targetMonth;
        $response = $this->actingAs($admin)->get($url);

        // ステータス確認
        $response->assertStatus(200);

        // 月表示（YYYY/MM）
        $response->assertSee($targetDate->format('Y/m'));

        // 勤怠情報の確認
        $response->assertSee($targetDate->format('m/d')); // 例：06/10
        $response->assertSee('10:00');
        $response->assertSee('19:00');
        $response->assertSee('1:00'); // 休憩
        $response->assertSee('8:00'); // 労働時間
    }

    /** @test */
    public function 「翌月」を押下した時に表示月の前月の情報が表示される()
    {
        // 管理者と一般ユーザーを作成
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        // 翌月の任意日を指定（翌月15日）
        $targetDate = now()->addMonth()->startOfMonth()->addDays(14); // 翌月15日
        $targetMonth = $targetDate->format('Y-m');

        // 勤怠データ作成
        $attendance = \App\Models\Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $targetDate->format('Y-m-d'),
            'clock_in_time' => '08:30:00',
            'clock_out_time' => '17:30:00',
        ]);

        // 休憩1時間
        \App\Models\BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start_time' => '12:00:00',
            'break_end_time' => '13:00:00',
        ]);

        // 翌月指定でアクセス
        $url = '/admin/users/' . $user->id . '/attendances?month=' . $targetMonth;
        $response = $this->actingAs($admin)->get($url);

        $response->assertStatus(200);

        // 翌月が表示されていることを確認
        $response->assertSee($targetDate->format('Y/m'));  // 表示月
        $response->assertSee($targetDate->format('m/d'));  // 勤怠日付
        $response->assertSee('08:30');
        $response->assertSee('17:30');
        $response->assertSee('1:00'); // 休憩
        $response->assertSee('8:00'); // 合計
    }

    /** @test */
    public function 管理者は勤怠の詳細ボタンを押下して勤怠詳細画面に遷移できる()
    {
        // 管理者と一般ユーザーを作成
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        // 勤怠データを1件作成
        $attendance = \App\Models\Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'clock_in_time' => '09:00:00',
            'clock_out_time' => '18:00:00',
        ]);

        // 休憩1時間
        \App\Models\BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start_time' => '12:00:00',
            'break_end_time' => '13:00:00',
        ]);

        // 勤怠一覧ページにアクセス（詳細リンクが表示されるページ）
        $listUrl = '/admin/users/' . $user->id . '/attendances';
        $response = $this->actingAs($admin)->get($listUrl);
        $response->assertStatus(200);

        // 「詳細」リンクの存在確認
        $response->assertSee('/attendance/detail/' . $attendance->id);

        // 遷移先へアクセスし、ステータス確認
        $detailUrl = '/attendance/detail/' . $attendance->id;
        $response = $this->actingAs($admin)->get($detailUrl);
        $response->assertStatus(200);
    }
}
