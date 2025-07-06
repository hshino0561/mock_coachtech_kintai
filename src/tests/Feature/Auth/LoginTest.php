<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    // use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = \App\Models\User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'テストユーザー',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
                'is_first_login' => false,
            ]
        );
    }

    public function test_2_1_メールアドレスが未入力の場合、バリデーションメッセージが表示される()
    {
        $response = $this->from('/login')->post('/login', [
            'login' => '',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['login']);
        $this->followRedirects($response)->assertSee('メールアドレスを入力してください');
    }

    public function test_2_2_パスワードが未入力の場合、バリデーションメッセージが表示される()
    {
        $response = $this->from('/login')->post('/login', [
            'login' => $this->user->email,
            'password' => '',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['password']);
        $this->followRedirects($response)->assertSee('パスワードを入力してください');
    }

    public function test_2_3_登録内容と一致しない場合、バリデーションメッセージが表示される()
    {
        $response = $this->from('/login')->post('/login', [
            'login' => 'wrong@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['login']);
        $this->followRedirects($response)->assertSee('ログイン情報が登録されていません');
    }
}
