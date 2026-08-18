<?php

namespace App\Models\Concerns;

use App\Models\SeoMeta;
use Illuminate\Support\Str;

trait HasSeoMeta
{
    public function seoMeta()
    {
        return $this->morphOne(SeoMeta::class, 'seoable');
    }

    public function getMetaTitleAttribute(): string
    {
        return $this->seoMeta?->meta_title ?: $this->name;
    }

    public function getMetaDescriptionAttribute(): string
    {
        if ($this->seoMeta?->meta_description) {
            return $this->seoMeta->meta_description;
        }

        return Str::limit(strip_tags($this->description ?? ''), 155);
    }

    public function getOgImageAttribute(): ?string
    {
        return $this->seoMeta?->og_image_path;
    }
}
