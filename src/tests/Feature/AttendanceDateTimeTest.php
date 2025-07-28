<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;

class AttendanceDateTimeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_現在の日時情報がUIと同じ形式で出力されている()
    {
        // Carbonのテスト用現在時刻を固定（例：2025年7月21日 14:38）
        $fixedNow = Carbon::create(2025, 7, 21, 14, 38);
        Carbon::setTestNow($fixedNow);

        // ロケールを日本語に（曜日表記のため）
        Carbon::setLocale('ja');

        // テスト用ユーザー作成 & ログイン
        $user = User::factory()->create([
            'role' => 'user'
        ]);
        $this->actingAs($user);

        // ページへアクセス
        $response = $this->get('/attendance');

        // 表示形式（ビューと同じ形式）を作成
        $expectedDate = $fixedNow->isoFormat('Y年M月D日(ddd)');
        $expectedTime = $fixedNow->format('H:i');

        // 日付と時刻がHTMLに含まれているか検証
        $response->assertStatus(200);
        $response->assertSeeText($expectedDate);
        $response->assertSeeText($expectedTime);
    }
}
