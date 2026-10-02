<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class Product extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'category_id', 'name', 'slug', 'short_description', 'description', 'image_media_id',
        'price', 'promotional_price', 'status', 'featured', 'sort_order', 'seo_title',
        'seo_description', 'canonical_url', 'og_title', 'og_description', 'og_media_id', 'robots_directive',
    ];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'promotional_price' => 'decimal:2', 'featured' => 'boolean', 'sort_order' => 'integer'];
    }

    protected static function booted(): void
    {
        static::saving(function (Product $product): void {
            $product->slug = Str::slug($product->slug ?: $product->name);

            Validator::make($product->only(['price', 'promotional_price']), [
                'price' => ['nullable', 'numeric', 'min:0'],
                'promotional_price' => ['nullable', 'numeric', 'min:0', 'lte:price'],
            ])->validate();
        });
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE)->whereHas('category', fn (Builder $category) => $category->active());
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
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
