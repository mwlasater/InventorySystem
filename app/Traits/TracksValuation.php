<?php

namespace App\Traits;

/**
 * Appends an item_valuations row whenever an item's estimated_value is first set
 * or later changed, building a queryable valuation series alongside the single
 * current value stored on the item itself.
 */
trait TracksValuation
{
    public static function bootTracksValuation(): void
    {
        static::created(function (self $item): void {
            if ($item->estimated_value !== null) {
                $item->recordValuation();
            }
        });

        static::updated(function (self $item): void {
            if ($item->wasChanged('estimated_value') && $item->estimated_value !== null) {
                $item->recordValuation();
            }
        });
    }

    public function recordValuation(): void
    {
        $this->valuations()->create([
            'value' => $this->estimated_value,
            'currency' => $this->purchase_currency ?: 'USD',
            'source' => $this->valuation_source,
            'valued_at' => $this->valuation_date ?: now()->toDateString(),
        ]);
    }
}
