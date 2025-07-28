<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoginUserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function メールアドレスが未入力の場合はバリデーションメッセージが表示される()
    {
        // テスト用ユーザーを登録
        \App\Models\User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'role' => 'user',
        ]);

        // メールアドレス未入力でログインを試みる
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        // エラー確認（バリデーションエラーでセッションに保存されているか）
        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function パスワードが未入力の場合はバリデーションエラーが発生する()
    {
        \App\Models\User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'role' => 'user',
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /** @test */
    public function 登録されていないメールアドレスでログインした場合はバリデーションエラーが発生する()
    {
        \App\Models\User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'role' => 'user',
        ]);

        $response = $this->post('/login', [
            'email' => 'wrong@example.com', // 存在しないメールアドレス
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }
}
