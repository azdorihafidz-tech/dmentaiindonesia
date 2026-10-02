<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class Outlet extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'name', 'slug', 'address', 'phone', 'whatsapp', 'business_hours', 'google_maps_url', 'latitude',
        'longitude', 'image_media_id', 'description', 'status', 'featured', 'sort_order', 'seo_title',
        'seo_description', 'canonical_url', 'og_title', 'og_description', 'og_media_id', 'robots_directive',
    ];

    protected function casts(): array
    {
        return ['latitude' => 'decimal:7', 'longitude' => 'decimal:7', 'featured' => 'boolean', 'sort_order' => 'integer'];
    }

    protected static function booted(): void
    {
        static::saving(function (Outlet $outlet): void {
            $outlet->slug = Str::slug($outlet->slug ?: $outlet->name);
            Validator::make($outlet->only(['latitude', 'longitude', 'google_maps_url']), [
                'latitude' => ['nullable', 'numeric', 'between:-90,90'],
                'longitude' => ['nullable', 'numeric', 'between:-180,180'],
                'google_maps_url' => ['nullable', 'url', 'max:2048'],
            ])->validate();
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_media_id');
    }

    public function ogMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'og_media_id');
    }
}
