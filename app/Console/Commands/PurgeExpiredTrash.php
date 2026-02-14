<?php

namespace App\Console\Commands;

use App\Models\Item;
use Illuminate\Console\Command;

class PurgeExpiredTrash extends Command
{
    protected $signature = 'trash:purge';
    protected $description = 'Permanently delete items that have been in trash for more than 90 days';

    public function handle(): int
    {
        $cutoff = now()->subDays(90);
        $items = Item::trashed()->where('deleted_at', '<', $cutoff)->get();

        $count = 0;
        foreach ($items as $item) {
            $item->tags()->detach();
            $item->photos()->delete();
            $item->documents()->delete();
            $item->delete();
            $count++;
        }

        $this->info("Purged {$count} expired trash items.");
        return Command::SUCCESS;
    }
}
