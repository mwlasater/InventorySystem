<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ItemPhoto extends Model
{
    protected $fillable = [
        'item_id',
        'file_path',
        'thumbnail_sm',
        'thumbnail_md',
        'caption',
        'is_primary',
        'sort_order',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    public function getThumbnailSmUrlAttribute(): ?string
    {
        return $this->thumbnail_sm ? Storage::url($this->thumbnail_sm) : null;
    }

    public function getThumbnailMdUrlAttribute(): ?string
    {
        return $this->thumbnail_md ? Storage::url($this->thumbnail_md) : null;
    }
}
