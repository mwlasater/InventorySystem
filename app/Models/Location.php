<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    protected $fillable = ['name', 'parent_id', 'level', 'description', 'sort_order'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Location::class, 'parent_id')->orderBy('sort_order');
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id')->orderBy('sort_order');
    }

    public function scopeBuildings($query)
    {
        return $query->where('level', 'building')->orderBy('sort_order');
    }

    public function getFullPathAttribute(): string
    {
        $parts = [$this->name];
        $current = $this;
        while ($current->parent) {
            $current = $current->parent;
            array_unshift($parts, $current->name);
        }
        return implode(' > ', $parts);
    }

    public function getItemCountAttribute(): int
    {
        $count = $this->items()->count();
        foreach ($this->children as $child) {
            $count += $child->item_count;
        }
        return $count;
    }

    public function getAllDescendantIds(): array
    {
        $ids = [$this->id];
        foreach ($this->children as $child) {
            $ids = array_merge($ids, $child->getAllDescendantIds());
        }
        return $ids;
    }
}
