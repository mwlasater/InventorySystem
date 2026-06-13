<?php

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Reminder that a loaned-out item is past its expected return date.
 *
 * Delivered on the `mail` and `database` channels. Queued (ShouldQueue) because
 * the daily scan may fan out many reminders at once and none are time-critical;
 * production processes the queue via the `queue:work --stop-when-empty` cron
 * documented in DEPLOYMENT.md.
 */
class OverdueLoanReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Transaction $transaction) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $item = $this->transaction->item;
        $dueDate = $this->transaction->expected_return_date;
        $daysOverdue = (int) $dueDate->diffInDays(now());
        $recipient = $this->transaction->recipient_name ?: 'someone';

        $mail = (new MailMessage)
            ->subject('Overdue loan: '.$item->name)
            ->greeting('Loan reminder')
            ->line("\"{$item->name}\" was loaned to {$recipient} and is now {$daysOverdue} ".str('day')->plural($daysOverdue).' overdue.')
            ->line('Expected return date: '.$dueDate->toFormattedDateString().'.');

        if ($this->transaction->recipient_contact) {
            $mail->line('Recipient contact: '.$this->transaction->recipient_contact);
        }

        return $mail
            ->action('View item', url('/items/'.$item->id))
            ->line('Record the return on the item page once it comes back to mark this resolved.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'transaction_id' => $this->transaction->id,
            'item_id' => $this->transaction->item_id,
            'item_name' => $this->transaction->item->name,
            'recipient_name' => $this->transaction->recipient_name,
            'expected_return_date' => optional($this->transaction->expected_return_date)->toDateString(),
        ];
    }
}
