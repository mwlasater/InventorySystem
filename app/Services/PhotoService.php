<?php

namespace App\Services;

use App\Models\ItemPhoto;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class PhotoService
{
    public function store(UploadedFile $file, int $itemId, ?string $caption = null): ItemPhoto
    {
        $disk = Storage::disk('public');

        // Store original
        $originalPath = $file->store("items/{$itemId}/photos", 'public');

        // Generate thumbnails
        $thumbnailSm = $this->generateThumbnail($file, $itemId, 150, 'sm');
        $thumbnailMd = $this->generateThumbnail($file, $itemId, 400, 'md');

        // Determine sort order
        $maxOrder = ItemPhoto::where('item_id', $itemId)->max('sort_order') ?? -1;

        // Check if this is the first photo (auto-set as primary)
        $isFirst = ItemPhoto::where('item_id', $itemId)->count() === 0;

        return ItemPhoto::create([
            'item_id' => $itemId,
            'file_path' => $originalPath,
            'thumbnail_sm' => $thumbnailSm,
            'thumbnail_md' => $thumbnailMd,
            'caption' => $caption,
            'is_primary' => $isFirst,
            'sort_order' => $maxOrder + 1,
            'uploaded_by' => auth()->id(),
        ]);
    }

    protected function generateThumbnail(UploadedFile $file, int $itemId, int $size, string $suffix): string
    {
        $image = Image::read($file->getRealPath());

        $image->scaleDown(width: $size, height: $size);

        $filename = pathinfo($file->hashName(), PATHINFO_FILENAME) . "_{$suffix}.jpg";
        $path = "items/{$itemId}/photos/thumbs/{$filename}";

        Storage::disk('public')->put($path, $image->toJpeg(80)->toString());

        return $path;
    }

    public function delete(ItemPhoto $photo): void
    {
        $disk = Storage::disk('public');

        // Delete all files
        if ($photo->file_path) {
            $disk->delete($photo->file_path);
        }
        if ($photo->thumbnail_sm) {
            $disk->delete($photo->thumbnail_sm);
        }
        if ($photo->thumbnail_md) {
            $disk->delete($photo->thumbnail_md);
        }

        $wasPrimary = $photo->is_primary;
        $itemId = $photo->item_id;

        $photo->delete();

        // If deleted photo was primary, set first remaining photo as primary
        if ($wasPrimary) {
            $nextPhoto = ItemPhoto::where('item_id', $itemId)
                ->orderBy('sort_order')
                ->first();
            if ($nextPhoto) {
                $nextPhoto->update(['is_primary' => true]);
            }
        }
    }

    public function setPrimary(ItemPhoto $photo): void
    {
        // Unset all other photos for this item
        ItemPhoto::where('item_id', $photo->item_id)
            ->where('id', '!=', $photo->id)
            ->update(['is_primary' => false]);

        $photo->update(['is_primary' => true]);
    }

    public function reorder(int $itemId, array $photoIds): void
    {
        foreach ($photoIds as $index => $photoId) {
            ItemPhoto::where('id', $photoId)
                ->where('item_id', $itemId)
                ->update(['sort_order' => $index]);
        }
    }

    public function getPhotoCount(int $itemId): int
    {
        return ItemPhoto::where('item_id', $itemId)->count();
    }

    public const MAX_PHOTOS_PER_ITEM = 20;
    public const MAX_FILE_SIZE_MB = 10;
}
