<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemPhoto;
use App\Services\PhotoService;
use Illuminate\Http\Request;

class ItemPhotoController extends Controller
{
    public function __construct(protected PhotoService $photoService)
    {
    }

    public function store(Request $request, Item $item)
    {
        $request->validate([
            'photos' => 'required|array|min:1',
            'photos.*' => 'image|max:10240', // 10MB
        ]);

        $currentCount = $this->photoService->getPhotoCount($item->id);
        $newCount = count($request->file('photos'));

        if ($currentCount + $newCount > PhotoService::MAX_PHOTOS_PER_ITEM) {
            return back()->withErrors([
                'photos' => 'Maximum ' . PhotoService::MAX_PHOTOS_PER_ITEM . ' photos per item. Currently have ' . $currentCount . '.',
            ]);
        }

        foreach ($request->file('photos') as $file) {
            $this->photoService->store($file, $item->id);
        }

        return back()->with('success', $newCount . ' photo(s) uploaded successfully.');
    }

    public function destroy(Item $item, ItemPhoto $photo)
    {
        if ($photo->item_id !== $item->id) {
            abort(404);
        }

        $this->photoService->delete($photo);

        return back()->with('success', 'Photo deleted.');
    }

    public function setPrimary(Item $item, ItemPhoto $photo)
    {
        if ($photo->item_id !== $item->id) {
            abort(404);
        }

        $this->photoService->setPrimary($photo);

        return back()->with('success', 'Primary photo updated.');
    }

    public function reorder(Request $request, Item $item)
    {
        $request->validate([
            'photo_ids' => 'required|array',
            'photo_ids.*' => 'integer|exists:item_photos,id',
        ]);

        $this->photoService->reorder($item->id, $request->photo_ids);

        return response()->json(['success' => true]);
    }

    public function updateCaption(Request $request, Item $item, ItemPhoto $photo)
    {
        if ($photo->item_id !== $item->id) {
            abort(404);
        }

        $request->validate([
            'caption' => 'nullable|string|max:255',
        ]);

        $photo->update(['caption' => $request->caption]);

        return back()->with('success', 'Caption updated.');
    }
}
