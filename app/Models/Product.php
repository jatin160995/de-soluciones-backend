<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Models\Concerns\HasSeoMeta;

class Product extends Model implements HasMedia
{
    use HasSeoMeta;
    use SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'store_id',
        'category_id',
        'name',
        'slug',
        'description',
        'base_price',
        'discounted_price',
        'status',
        'is_featured',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'base_price' => 'decimal:2',
        'discounted_price' => 'decimal:2',
    ];



    public function getEffectivePriceAttribute(): string
    {
        return $this->discounted_price ?? $this->base_price;
    }
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->useFallbackUrl('/images/placeholder-product.png');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->nonQueued()
            ->performOnCollections('images');
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
}
