<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoginAdminTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function メールアドレスが未入力の場合はバリデーションメッセージが表示される()
    {
        // 管理者ユーザーを登録
        \App\Models\User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        // メールアドレス未入力でログインを試みる
        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        // バリデーションエラーメッセージを確認
        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    /** @test */
    public function パスワードが未入力の場合はバリデーションメッセージが表示される()
    {
        // 管理者ユーザーを登録
        \App\Models\User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('adminpass'),
            'role' => 'admin',
        ]);

        // パスワード未入力でログインを試みる
        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => '',
        ]);

        // エラー確認（バリデーションメッセージ）
        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    /** @test */
    public function 登録内容と一致しない場合はバリデーションメッセージが表示される()
    {
        // 管理者ユーザーを登録
        \App\Models\User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('adminpass'),
            'role' => 'admin',
        ]);

        // 誤ったメールアドレスでログインを試みる
        $response = $this->post('/admin/login', [
            'email' => 'wrong@example.com',
            'password' => 'adminpass',
        ]);

        // エラー確認（認証失敗）
        $response->assertSessionHasErrors(['email' => 'ログイン情報が登録されていません']);
    }
}
