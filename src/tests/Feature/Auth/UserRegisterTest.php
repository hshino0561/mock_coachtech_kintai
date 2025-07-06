<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserRegisterTest extends TestCase
{
    protected string $url = '/register';

    // use RefreshDatabase;

    public function test_1_1_名前が未入力の場合、バリデーションメッセージが表示される()
    {
        $response = $this->from('/register')->post('/register', [
            // intentionally omit 'name'
            'email' => 'test' . uniqid() . '@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Fortifyはバリデーションエラー時に302でリダイレクト
        $response->assertRedirect('/register');
        $response->assertSessionHasErrors(['name']);

        $this->followRedirects($response)->assertSee('お名前を入力してください');
    }

    public function test_1_2_メールアドレスが未入力の場合、バリデーションメッセージが表示される()
    {
        $response = $this->from('/register')->post('/register', [
            // intentionally omit 'name'
            'email' => 'test' . uniqid() . '@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Fortifyはバリデーションエラー時に302でリダイレクト
        $response->assertRedirect('/register');
        $response->assertSessionHasErrors(['name']);

        $this->followRedirects($response)->assertSee('お名前を入力してください');

        $response = $this->from('/register')->post('/register', [
            'name' => 'テストユーザー',
            'email' => '', // メールアドレス未入力
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // バリデーションエラーがセッションに含まれているか確認
        $response->assertRedirect('/register');
        $response->assertSessionHasErrors(['email']);

        // メッセージが表示されることを確認
        $this->followRedirects($response)
            ->assertSee('メールアドレスを入力してください');
    }

    public function test_1_3_パスワードが8文字未満の場合、バリデーションメッセージが表示される()
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'short7',
            'password_confirmation' => 'short7',
        ]);

        // バリデーションエラーがセッションに含まれているか確認（両方）
        $response->assertRedirect('/register');
        $response->assertSessionHasErrors(['password', 'password_confirmation']);

        // メッセージが表示されることを確認（両方）
        $this->followRedirects($response)->assertSee('パスワードは8文字以上で入力してください');
    }

    public function test_1_4_パスワードが一致しない場合、バリデーションメッセージが表示される()
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
        ]);

        // ★エラーバッグを確認
        // dd(session('errors')->getBag('default')->keys());

        // バリデーションエラーがセッションに含まれているか確認
        $response->assertRedirect('/register');
        $response->assertSessionHasErrors(['password']);

        // メッセージが表示されることを確認（両方）
        $this->followRedirects($response)->assertSee('パスワードと一致しません');
    }

    public function test_1_5_パスワードが未入力の場合、バリデーションメッセージが表示される()
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        // バリデーションエラーがセッションに含まれているか確認（両方）
        $response->assertRedirect('/register');
        $response->assertSessionHasErrors(['password', 'password_confirmation']);

        // メッセージが表示されることを確認（両方）
        $this->followRedirects($response)->assertSee('パスワードを入力してください');
        // $this->followRedirects($response)->assertSee('確認用パスワードを入力してください');
    }

    public function test_1_6_フォームに内容が入力されていた場合、データが正常に保存される()
    {
        $email = 'user' . uniqid() . '@example.com';
        $password = 'pass12345';
    
        $response = $this->from('/register')->post('/register', [
            'name' => 'テストユーザー',
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password,
        ]);
    
        // 成功時のリダイレクト先を確認
        $response->assertRedirect('/email/verify');
    
        // 登録されたユーザーがDBに存在することを確認
        $this->assertDatabaseHas('users', [
            'email' => $email,
        ]);
    }
}
