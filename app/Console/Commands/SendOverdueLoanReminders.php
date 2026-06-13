<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Notifications\OverdueLoanReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendOverdueLoanReminders extends Command
{
    protected $signature = 'loans:remind-overdue {--days=7 : Minimum days between repeat reminders for the same loan}';

    protected $description = 'Notify loan creators about items that are overdue for return';

    public function handle(): int
    {
        $cadence = (int) $this->option('days');
        $remindBefore = now()->subDays($cadence);

        // Overdue loans not reminded recently (never, or older than the cadence).
        $loans = Transaction::overdueLoans()
            ->where(function ($q) use ($remindBefore) {
                $q->whereNull('overdue_reminder_sent_at')
                    ->orWhere('overdue_reminder_sent_at', '<', $remindBefore);
            })
            ->with(['item', 'creator'])
            ->get();

        $sent = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($loans as $loan) {
            $creator = $loan->creator;

            if (! $creator || ! $creator->email) {
                // No internal user to notify (creator deleted or address missing).
                $skipped++;

                continue;
            }

            try {
                // Synchronous send: only stamp the loan once delivery succeeds, so
                // a failed send (e.g. SMTP down) is retried on the next daily run
                // rather than being silently marked as reminded.
                $creator->notify(new OverdueLoanReminder($loan));
                $loan->forceFill(['overdue_reminder_sent_at' => now()])->save();
                $sent++;
            } catch (Throwable $e) {
                $failed++;
                Log::error("Failed to send overdue-loan reminder for transaction {$loan->id}: ".$e->getMessage());
            }
        }

        $this->info("Sent {$sent} overdue-loan ".str('reminder')->plural($sent).
            ($skipped ? " ({$skipped} skipped: no notifiable creator)" : '').
            ($failed ? " ({$failed} failed to send — will retry next run)" : '').'.');

        return $failed > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
