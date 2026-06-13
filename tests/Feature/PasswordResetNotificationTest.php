<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_reset_request_sends_branded_notification(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'owner@example.test']);

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHasNoErrors();

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_reset_notification_mail_uses_app_branded_subject(): void
    {
        $user = User::factory()->create();
        $mail = (new ResetPasswordNotification('test-token'))->toMail($user);

        $this->assertSame(
            'Reset your '.config('app.name').' password',
            $mail->subject
        );
    }
}
