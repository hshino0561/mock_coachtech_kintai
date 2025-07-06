<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
// use RefreshDatabase; は無効化のまま
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

class AdminLoginTest extends TestCase
{
    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var \App\Models\Admin $admin */
        $this->admin = Admin::firstOrCreate(
            ['email' => 'admin1@admin.com'],
            [
                'name' => 'admin1',
                'password' => Hash::make('pass'), // DBにはハッシュで保存されている前提
            ]
        );
    }

    public function test_2_1_メールアドレスが未入力の場合、バリデーションメッセージが表示される()
    {
        $response = $this->from('/admin/login')->post('/admin/login', [
            'login' => '',
            'password' => 'pass',
        ]);

        $response->assertRedirect('/admin/login');
        $response->assertSessionHasErrors(['login']);
        $this->followRedirects($response)->assertSee('メールアドレスを入力してください');
    }

    public function test_2_2_パスワードが未入力の場合、バリデーションメッセージが表示される()
    {
        $response = $this->from('/admin/login')->post('/admin/login', [
            'login' => $this->admin->email,
            'password' => '',
        ]);

        $response->assertRedirect('/admin/login');
        $response->assertSessionHasErrors(['password']);
        $this->followRedirects($response)->assertSee('パスワードを入力してください');
    }

    public function test_2_3_登録内容と一致しない場合、バリデーションメッセージが表示される()
    {
        $response = $this->from('/admin/login')->post('/admin/login', [
            'login' => 'wrong@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect('/admin/login');
        $response->assertSessionHasErrors(['login']);
        $this->followRedirects($response)->assertSee('ログイン情報が登録されていません');
    }
}
