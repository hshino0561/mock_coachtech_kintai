<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
// use RefreshDatabase;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;

class EmailVerificationTest extends TestCase
{
    use WithFaker;

    /**
     * @test
     */
    public function test_16_1_会員登録後_認証メールが送信される()
    {
        Notification::fake(); // 通知のフェイク（Mail::fake() ではなく）
    
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => $email = 'user' . uniqid() . '@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
    
        $response->assertRedirect('/email/verify');
    
        $user = User::where('email', $email)->firstOrFail();
    
        // メール認証通知が送信されたかを確認
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /**
     * @test
     */
    public function test_16_2_メール認証誘導画面で「認証はこちらから」ボタンを押下するとメール認証サイトに遷移する()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->unverified()->create();

        $this->actingAs($user);

        $response = $this->get('/email/verify');

        $response->assertStatus(200);
        $response->assertSee('認証はこちらから'); // ボタンの文言
        $response->assertSee('form'); // フォームがある前提
    }

    /**
     * @test
     */
    public function test_16_3_メール認証サイトのメール認証を完了すると、勤怠画面に遷移する()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->unverified()->create();

        // 署名付き認証リンクを生成
        $verifyUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())]
        );

        $this->actingAs($user);

        $response = $this->get($verifyUrl);

        $response->assertRedirect('/attendance');

        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}
