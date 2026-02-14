<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportService
{
    public function toCsv(Collection $data, string $filename, array $headers = []): StreamedResponse
    {
        return response()->streamDownload(function () use ($data, $headers) {
            $handle = fopen('php://output', 'w');

            if (!empty($headers)) {
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
