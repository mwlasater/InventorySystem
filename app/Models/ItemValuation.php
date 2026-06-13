<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A point-in-time record of an item's estimated value. Rows are append-only:
 * one is written each time an item's estimated_value is set or changed, so the
 * series is never overwritten the way items.estimated_value is.
 */
class ItemValuation extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'value',
        'currency',
        'source',
        'valued_at',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'valued_at' => 'date',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
