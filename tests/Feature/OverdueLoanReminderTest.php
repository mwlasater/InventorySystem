<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\OverdueLoanReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OverdueLoanReminderTest extends TestCase
{
    use RefreshDatabase;

    private function overdueLoan(User $creator): Transaction
    {
        $item = Item::factory()->status('loaned_out')->create();

        return Transaction::factory()
            ->loanedOut(now()->subDays(10)->toDateString())
            ->create(['item_id' => $item->id, 'created_by' => $creator->id]);
    }

    public function test_sends_reminder_to_loan_creator_and_records_timestamp(): void
    {
        Notification::fake();
        $creator = User::factory()->create();
        $loan = $this->overdueLoan($creator);

        $this->artisan('loans:remind-overdue')->assertSuccessful();

        Notification::assertSentTo($creator, OverdueLoanReminder::class);
        $this->assertNotNull($loan->fresh()->overdue_reminder_sent_at);
    }

    public function test_does_not_remind_again_within_cadence(): void
    {
        Notification::fake();
        $creator = User::factory()->create();
        $loan = $this->overdueLoan($creator);
        $loan->forceFill(['overdue_reminder_sent_at' => now()->subDays(2)])->save();

        $this->artisan('loans:remind-overdue --days=7')->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_reminds_again_after_cadence_elapses(): void
    {
        Notification::fake();
        $creator = User::factory()->create();
        $loan = $this->overdueLoan($creator);
        $loan->forceFill(['overdue_reminder_sent_at' => now()->subDays(10)])->save();

        $this->artisan('loans:remind-overdue --days=7')->assertSuccessful();

        Notification::assertSentTo($creator, OverdueLoanReminder::class);
    }

    public function test_ignores_returned_loans(): void
    {
        Notification::fake();
        $creator = User::factory()->create();
        // Item back in collection => loan no longer outstanding.
        $item = Item::factory()->status('in_collection')->create();
        Transaction::factory()
            ->loanedOut(now()->subDays(10)->toDateString())
            ->create(['item_id' => $item->id, 'created_by' => $creator->id]);

        $this->artisan('loans:remind-overdue')->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_ignores_loans_not_yet_due(): void
    {
        Notification::fake();
        $creator = User::factory()->create();
        $item = Item::factory()->status('loaned_out')->create();
        Transaction::factory()
            ->loanedOut(now()->addDays(5)->toDateString())
            ->create(['item_id' => $item->id, 'created_by' => $creator->id]);

        $this->artisan('loans:remind-overdue')->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_mail_renders_with_item_and_overdue_details(): void
    {
        $creator = User::factory()->create();
        $loan = $this->overdueLoan($creator);
        $loan->load('item');

        $mail = (new OverdueLoanReminder($loan))->toMail($creator);

        $this->assertSame('Overdue loan: '.$loan->item->name, $mail->subject);
        $this->assertStringContainsString('10 days overdue', implode("\n", $mail->introLines));
    }

    public function test_skips_loan_with_no_notifiable_creator(): void
    {
        Notification::fake();
        $item = Item::factory()->status('loaned_out')->create();
        $loan = Transaction::factory()
            ->loanedOut(now()->subDays(10)->toDateString())
            ->create(['item_id' => $item->id, 'created_by' => null]);

        $this->artisan('loans:remind-overdue')->assertSuccessful();

        Notification::assertNothingSent();
        $this->assertNull($loan->fresh()->overdue_reminder_sent_at);
    }

    public function test_failed_send_is_not_stamped_and_is_retried(): void
    {
        $creator = User::factory()->create();
        $loan = $this->overdueLoan($creator);

        // Make notification delivery throw (e.g. SMTP down).
        $dispatcher = \Mockery::mock(\Illuminate\Contracts\Notifications\Dispatcher::class);
        $dispatcher->shouldReceive('send')->andThrow(new \RuntimeException('smtp down'));
        $this->app->instance(\Illuminate\Contracts\Notifications\Dispatcher::class, $dispatcher);

        $this->artisan('loans:remind-overdue')
            ->expectsOutputToContain('failed to send')
            ->assertExitCode(1);

        // Left unstamped so the next daily run retries instead of skipping it.
        $this->assertNull($loan->fresh()->overdue_reminder_sent_at);
    }
}
