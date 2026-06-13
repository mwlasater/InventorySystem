<?php

namespace App\Services;

use App\Models\Item;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportService
{
    /**
     * The inventory CSV column set, shared by the full export and the
     * bulk "export selection" action so they never drift apart.
     *
     * @param  Collection<int, Item>  $items
     */
    public function itemsToCsv(Collection $items, string $filename): StreamedResponse
    {
        $headers = [
            'Name', 'Description', 'Category', 'SKU', 'Barcode', 'Condition',
            'Brand', 'Model Number', 'Year Manufactured', 'Color', 'Dimensions',
            'Quantity', 'Acquisition Date', 'Acquisition Source', 'Acquisition Method',
            'Purchase Price', 'Purchase Currency', 'Estimated Value', 'Valuation Date',
            'Valuation Source', 'Status', 'Location', 'Tags', 'Notes',
        ];

        $data = $items->map(fn (Item $item) => [
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
        ]);

        return $this->toCsv($data, $filename, $headers);
    }

    public function toCsv(Collection $data, string $filename, array $headers = []): StreamedResponse
    {
        return response()->streamDownload(function () use ($data, $headers) {
            $handle = fopen('php://output', 'w');

            if (! empty($headers)) {
                fputcsv($handle, $headers);
            }

            foreach ($data as $row) {
                if (is_array($row)) {
                    fputcsv($handle, array_values($row));
                } else {
                    fputcsv($handle, (array) $row);
                }
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function toPdf(string $view, array $data, string $filename)
    {
        $pdf = Pdf::loadView($view, $data)
            ->setPaper('letter', 'landscape');

        return $pdf->download($filename);
    }
}
