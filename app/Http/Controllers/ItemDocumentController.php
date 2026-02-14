<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ItemDocumentController extends Controller
{
    public function store(Request $request, Item $item)
    {
        $request->validate([
            'document' => 'required|file|max:20480|mimes:pdf,doc,docx,xls,xlsx,txt,csv,jpg,jpeg,png,gif,webp',
            'label' => 'nullable|string|max:255',
        ]);

        $file = $request->file('document');
        $path = $file->store("items/{$item->id}/documents", 'public');

        ItemDocument::create([
            'item_id' => $item->id,
            'file_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'label' => $request->label ?? $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'uploaded_by' => auth()->id(),
        ]);

        return back()->with('success', 'Document uploaded successfully.');
    }

    public function download(Item $item, ItemDocument $document)
    {
        if ($document->item_id !== $item->id) {
            abort(404);
        }

        return Storage::disk('public')->download(
            $document->file_path,
            $document->original_filename
        );
    }

    public function destroy(Item $item, ItemDocument $document)
    {
        if ($document->item_id !== $item->id) {
            abort(404);
        }

        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return back()->with('success', 'Document deleted.');
    }
}
