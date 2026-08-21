<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class HeroBanner extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'title',
        'link_type',
        'product_id',
        'category_id',
        'external_url',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('banner')
            ->width(1600)
            ->height(700)
            ->nonQueued()
            ->performOnCollections('image');
    }

    /**
     * Resolve where this slide should link to, based on link_type.
     */
    public function getLinkUrlAttribute(): ?string
    {
        return match ($this->link_type) {
            // 'product' => $this->product ? route('producto.show', $this->product->slug) : null,
            // 'category' => $this->category ? '/catalogo?categoria=' . $this->category->slug : null,
            // 'url' => $this->external_url,
            default => null,
        };
    }
}
