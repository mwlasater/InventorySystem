<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Services\ExportService;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function __construct(
        protected ExportService $exportService,
    ) {}

    public function index()
    {
        return view('export.index');
    }

    public function items(Request $request)
    {
        $items = Item::active()
            ->with(['category', 'location', 'tags'])
            ->get();

        return $this->exportService->itemsToCsv($items, 'inventory-export-'.now()->format('Y-m-d').'.csv');
    }
}
