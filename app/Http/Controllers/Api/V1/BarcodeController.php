<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ItemResource;
use App\Models\Item;
use Illuminate\Http\Request;

class BarcodeController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string|max:255',
        ]);

        $code = $request->string('barcode')->toString();

        $item = Item::active()
            ->where(fn ($q) => $q->where('barcode', $code)->orWhere('sku', $code))
            ->with(['category', 'location', 'tags'])
            ->first();

        if (! $item) {
            return response()->json(['message' => 'No item found for that barcode.'], 404);
        }

        return new ItemResource($item);
    }
}
