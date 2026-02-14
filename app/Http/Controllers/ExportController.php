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

        $headers = [
            'Name',
            'Description',
            'Category',
            'SKU',
            'Barcode',
            'Condition',
            'Brand',
            'Model Number',
            'Year Manufactured',
            'Color',
            'Dimensions',
            'Quantity',
            'Acquisition Date',
            'Acquisition Source',
            'Acquisition Method',
            'Purchase Price',
            'Purchase Currency',
            'Estimated Value',
            'Valuation Date',
            'Valuation Source',
            'Status',
            'Location',
            'Tags',
            'Notes',
        ];

        $data = $items->map(function (Item $item) {
            return [
                'Name' => $item->name,
                'Description' => $item->description,
                'Category' => $item->category?->name,
                'SKU' => $item->sku,
                'Barcode' => $item->barcode,
                'Condition' => $item->condition_label,
                'Brand' => $item->brand,
                'Model Number' => $item->model_number,
                'Year Manufactured' => $item->year_manufactured,
                'Color' => $item->color,
                'Dimensions' => $item->dimensions,
                'Quantity' => $item->quantity,
                'Acquisition Date' => $item->acquisition_date?->format('Y-m-d'),
                'Acquisition Source' => $item->acquisition_source,
                'Acquisition Method' => $item->acquisition_method,
                'Purchase Price' => $item->purchase_price,
                'Purchase Currency' => $item->purchase_currency,
                'Estimated Value' => $item->estimated_value,
                'Valuation Date' => $item->valuation_date?->format('Y-m-d'),
                'Valuation Source' => $item->valuation_source,
                'Status' => $item->status_label,
                'Location' => $item->location?->full_path,
                'Tags' => $item->tags->pluck('name')->implode(', '),
                'Notes' => $item->notes,
            ];
        });

        $filename = 'inventory-export-' . now()->format('Y-m-d') . '.csv';

        return $this->exportService->toCsv($data, $filename, $headers);
    }
}
