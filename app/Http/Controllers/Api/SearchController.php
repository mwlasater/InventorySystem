<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100',
        ]);

        $search = $request->q;

        $items = Item::active()
            ->where(function ($query) use ($search) {
                $query->whereRaw('MATCH(name, description, notes) AGAINST(? IN BOOLEAN MODE)', [$search . '*'])
                    ->orWhere('barcode', $search)
                    ->orWhere('sku', $search)
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('model_number', 'like', "%{$search}%");
            })
            ->with('category')
            ->select('id', 'name', 'sku', 'barcode', 'category_id', 'status')
            ->limit(10)
            ->get();

        return response()->json([
            'results' => $items->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'sku' => $item->sku,
                'category' => $item->category?->name,
                'status' => $item->status_label,
                'url' => route('items.show', $item),
            ]),
        ]);
    }
}
