<?php

namespace App\Services;

use App\Models\Item;
use Illuminate\Support\Collection;

class DuplicateDetectionService
{
    public function findPotentialDuplicates(array $data, ?int $excludeId = null): Collection
    {
        $duplicates = collect();

        // Check barcode match
        if (!empty($data['barcode'])) {
            $query = Item::active()->where('barcode', $data['barcode']);
            if ($excludeId) $query->where('id', '!=', $excludeId);
            $barcodeMatches = $query->get();
            $duplicates = $duplicates->merge($barcodeMatches);
        }

        // Check SKU match
        if (!empty($data['sku'])) {
            $query = Item::active()->where('sku', $data['sku']);
            if ($excludeId) $query->where('id', '!=', $excludeId);
            $skuMatches = $query->get();
            $duplicates = $duplicates->merge($skuMatches);
        }

        // Check similar name
        if (!empty($data['name'])) {
            $query = Item::active()->where('name', 'like', '%' . $data['name'] . '%');
            if ($excludeId) $query->where('id', '!=', $excludeId);
            $nameMatches = $query->limit(5)->get();
            $duplicates = $duplicates->merge($nameMatches);
        }

        return $duplicates->unique('id');
    }
}
