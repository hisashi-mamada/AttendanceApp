<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Illuminate\Support\Facades\URL;


class EmailVerificationFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 会員登録後、認証メールが送信される()
    {
        Notification::fake();

        // ユーザー登録処理（Fortifyの登録フォームを想定）
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // ユーザーが作成されているか確認
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        // ユーザーを取得して通知の検証
        $user = User::where('email', 'test@example.com')->first();

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /** @test */
    public function メール認証誘導画面で「認証はこちらから」ボタンを押下するとメール認証サイトに遷移する()
    {
        // ログイン済み未認証ユーザーを作成
        $user = \App\Models\User::factory()->create([
            'email_verified_at' => null,
            'role' => 'user',
        ]);


        // ログインしてメール認証画面にアクセス
        $response = $this->actingAs($user)->get('/email/verify');

        $response->assertStatus(200);

        // 「認証はこちらから」ボタンのリンクが含まれていることを確認
        $response->assertSee('認証はこちらから');
        $response->assertSee('http://localhost:8025');
    }


    public function test_メール認証サイトのメール認証を完了すると、勤怠登録画面に遷移する()
    {
        // 認証済みユーザーを作成
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'role' => 'user',
        ]);

        // ログイン状態で勤怠登録画面にアクセス
        $response = $this->actingAs($user)->get('/attendance');

        // アクセス成功を確認（200 OK）
        $response->assertStatus(200);

        // 表示されるビューが想定通りか確認
        $response->assertViewIs('items.attendance');
    }
}
