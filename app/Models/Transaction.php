<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    const TYPE_LABELS = [
        'sold' => 'Sold',
        'given_away' => 'Given Away',
        'traded' => 'Traded',
        'loaned_out' => 'Loaned Out',
        'returned' => 'Returned',
        'lost' => 'Reported Lost',
        'disposed' => 'Disposed',
        'status_correction' => 'Status Correction',
    ];

    const DISPOSITION_TYPES = ['sold', 'given_away', 'traded', 'loaned_out', 'lost', 'disposed'];
    const RESTORATION_TYPES = ['returned', 'status_correction'];

    const TYPE_TO_STATUS = [
        'sold' => 'sold',
        'given_away' => 'given_away',
        'traded' => 'traded',
        'loaned_out' => 'loaned_out',
        'returned' => 'in_collection',
        'lost' => 'lost',
        'disposed' => 'disposed',
        'status_correction' => 'in_collection',
    ];

    protected $fillable = [
        'item_id',
        'transaction_type',
        'transaction_date',
        'recipient_name',
        'recipient_contact',
        'sale_price',
        'shipping_cost',
        'platform',
        'net_proceeds',
        'expected_return_date',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'expected_return_date' => 'date',
            'sale_price' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'net_proceeds' => 'decimal:2',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->transaction_type] ?? $this->transaction_type;
    }

    public function isDisposition(): bool
    {
        return in_array($this->transaction_type, self::DISPOSITION_TYPES);
    }

    public function isRestoration(): bool
    {
        return in_array($this->transaction_type, self::RESTORATION_TYPES);
    }

    public function getResultingStatus(): string
    {
        return self::TYPE_TO_STATUS[$this->transaction_type] ?? 'in_collection';
    }

    public function isLoanOverdue(): bool
    {
        return $this->transaction_type === 'loaned_out'
            && $this->expected_return_date
            && $this->expected_return_date->isPast()
            && !$this->item->transactions()
                ->where('transaction_type', 'returned')
                ->where('created_at', '>', $this->created_at)
                ->exists();
    }
}
