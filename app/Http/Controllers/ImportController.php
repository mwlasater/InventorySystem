<?php

namespace App\Http\Controllers;

use App\Services\ImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImportController extends Controller
{
    public function __construct(
        protected ImportService $importService,
    ) {}

    public function index()
    {
        return view('import.index');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:10240',
        ]);

        $path = $request->file('file')->store('imports', 'local');

        $headers = $this->importService->parseHeaders(Storage::disk('local')->path($path));
        $preview = $this->importService->previewRows(Storage::disk('local')->path($path));

        session([
            'import_file_path' => $path,
            'import_headers' => $headers,
        ]);

        return redirect()->route('import.map');
    }

    public function map()
    {
        $headers = session('import_headers', []);
        $suggestions = $this->importService->suggestMappings($headers);
        $filePath = session('import_file_path');
        $preview = $this->importService->previewRows(Storage::disk('local')->path($filePath));
        $importableFields = ImportService::IMPORTABLE_FIELDS;

        return view('import.map', compact('headers', 'suggestions', 'preview', 'importableFields'));
    }

    public function validate(Request $request)
    {
        $request->validate([
            'mappings' => 'required|array',
        ]);

        $mappings = $request->input('mappings');

        session(['import_mappings' => $mappings]);

        $filePath = session('import_file_path');
        $results = $this->importService->validate(
            Storage::disk('local')->path($filePath),
            $mappings,
        );

        return view('import.validate', compact('results'));
    }

    public function execute(Request $request)
    {
        $filePath = session('import_file_path');
        $mappings = session('import_mappings');

        $results = $this->importService->execute(
            Storage::disk('local')->path($filePath),
            $mappings,
            auth()->id(),
        );

        Storage::disk('local')->delete($filePath);

        session()->forget(['import_file_path', 'import_headers', 'import_mappings']);

        $message = "{$results['imported']} items imported successfully.";
        if ($results['skipped'] > 0) {
            $message .= " {$results['skipped']} rows skipped due to errors.";
        }

        return redirect()->route('items.index')->with('success', $message);
    }
}
