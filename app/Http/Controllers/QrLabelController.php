<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrLabelController extends Controller
{
    public function show(Item $item)
    {
        $qrCode = QrCode::format('svg')
            ->size(200)
            ->generate(route('items.show', $item));

        return view('items.qr-label', [
            'item' => $item,
            'qrCode' => $qrCode,
        ]);
    }

    public function batchPrint(Request $request)
    {
        $request->validate([
            'item_ids' => 'required|array|min:1',
            'item_ids.*' => 'integer|exists:items,id',
        ]);

        $items = Item::whereIn('id', $request->item_ids)->get();

        $labels = $items->map(function ($item) {
            return [
                'item' => $item,
                'qrCode' => QrCode::format('svg')
                    ->size(150)
                    ->generate(route('items.show', $item)),
            ];
        });

        return view('items.qr-batch', [
            'labels' => $labels,
        ]);
    }
}
