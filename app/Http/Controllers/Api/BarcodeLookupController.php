<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;

class BarcodeLookupController extends Controller
{
    public function lookup(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string|max:255',
        ]);

        $barcode = $request->barcode;

        // Search by exact barcode match
        $item = Item::active()
            ->where('barcode', $barcode)
            ->first();

        if ($item) {
            return response()->json([
                'found' => true,
                'item' => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'url' => route('items.show', $item),
                    'status' => $item->status_label,
                    'location' => $item->location?->full_path,
                ],
            ]);
        }

        // Also check SKU
        $item = Item::active()
            ->where('sku', $barcode)
            ->first();

        if ($item) {
            return response()->json([
                'found' => true,
                'item' => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'url' => route('items.show', $item),
                    'status' => $item->status_label,
                    'location' => $item->location?->full_path,
                ],
            ]);
        }

        return response()->json([
            'found' => false,
            'barcode' => $barcode,
            'create_url' => route('items.create', ['barcode' => $barcode]),
        ]);
    }
}
