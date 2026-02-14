<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    const DISPOSITION_STATUSES = ['sold', 'given_away', 'traded', 'loaned_out', 'lost', 'disposed'];

    const CONDITION_LABELS = [
        'new' => 'New',
        'like_new' => 'Like New',
        'very_good' => 'Very Good',
        'good' => 'Good',
        'fair' => 'Fair',
        'poor' => 'Poor',
        'for_parts' => 'For Parts',
    ];

    const STATUS_LABELS = [
        'in_collection' => 'In Collection',
        'sold' => 'Sold',
        'given_away' => 'Given Away',
        'traded' => 'Traded',
        'loaned_out' => 'Loaned Out',
        'lost' => 'Lost',
        'damaged' => 'Damaged',
        'disposed' => 'Disposed',
    ];

    const ACQUISITION_METHODS = [
        'purchased' => 'Purchased',
        'gift' => 'Gift',
        'trade' => 'Trade',
        'found' => 'Found',
        'inherited' => 'Inherited',
        'other' => 'Other',
    ];

    protected $fillable = [
        'name', 'description', 'category_id', 'sku', 'barcode',
        'condition_rating', 'brand', 'model_number', 'year_manufactured',
        'color', 'dimensions', 'quantity', 'acquisition_date',
        'acquisition_source', 'acquisition_method', 'purchase_price',
        'purchase_currency', 'estimated_value', 'valuation_date',
        'valuation_source', 'status', 'location_id', 'notes',
        'is_favorite', 'is_deleted', 'deleted_at', 'created_by', 'modified_by',
    ];

    protected function casts(): array
    {
        return [
            'acquisition_date' => 'date',
            'valuation_date' => 'date',
            'deleted_at' => 'datetime',
            'purchase_price' => 'decimal:2',
            'estimated_value' => 'decimal:2',
            'is_favorite' => 'boolean',
            'is_deleted' => 'boolean',
            'quantity' => 'integer',
        ];
    }

    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'item_tags');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ItemPhoto::class)->orderBy('sort_order');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ItemDocument::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class)->orderBy('transaction_date', 'desc');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modified_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }

    public function scopeTrashed($query)
    {
        return $query->where('is_deleted', true);
    }

    public function scopeInCollection($query)
    {
        return $query->active()->where('status', 'in_collection');
    }

    public function scopeFavorites($query)
    {
        return $query->active()->where('is_favorite', true);
    }

    // Accessors
    public function getConditionLabelAttribute(): ?string
    {
        return self::CONDITION_LABELS[$this->condition_rating] ?? null;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function getAcquisitionMethodLabelAttribute(): ?string
    {
        return self::ACQUISITION_METHODS[$this->acquisition_method] ?? null;
    }

    public function getPrimaryPhotoAttribute()
    {
        return $this->photos->firstWhere('is_primary', true) ?? $this->photos->first();
    }

    public function getGainLossAttribute(): ?float
    {
        if ($this->estimated_value !== null && $this->purchase_price !== null) {
            return $this->estimated_value - $this->purchase_price;
        }
        return null;
    }

    // Methods
    public function softDelete(): void
    {
        $this->update([
            'is_deleted' => true,
            'deleted_at' => now(),
        ]);
    }

    public function restore(): void
    {
        $this->update([
            'is_deleted' => false,
            'deleted_at' => null,
        ]);
    }

    public function isDispositionStatus(): bool
    {
        return in_array($this->status, self::DISPOSITION_STATUSES);
    }
}
