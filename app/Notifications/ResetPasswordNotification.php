<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Branded password-reset email. Replaces the framework default so the copy
 * matches the application instead of saying "Reset Password Notification"
 * with no context. Sent synchronously (not queued) because it is on the
 * critical path — a user waiting on the reset link should not depend on the
 * queue worker cron having run.
 */
class ResetPasswordNotification extends BaseResetPassword
{
    protected function buildMailMessage($url): MailMessage
    {
        $appName = config('app.name');
        $expire = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->subject("Reset your {$appName} password")
            ->greeting('Password reset requested')
            ->line("We received a request to reset the password for your {$appName} account.")
            ->action('Reset password', $url)
            ->line("This link expires in {$expire} minutes.")
            ->line('If you did not request a password reset, no action is required — your password will stay the same.');
    }
}
