<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Tag extends Model
{
    protected $fillable = ['name', 'slug'];

    public static function booted(): void
    {
        static::creating(function (Tag $tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'item_tags');
    }

    public static function findOrCreateByName(string $name): self
    {
        $slug = Str::slug($name);
        return self::firstOrCreate(['slug' => $slug], ['name' => trim($name), 'slug' => $slug]);
    }
}
