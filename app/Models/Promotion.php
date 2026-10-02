<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class Promotion extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'title', 'slug', 'short_description', 'description', 'banner_media_id', 'cta_text', 'cta_url',
        'start_at', 'end_at', 'status', 'featured', 'sort_order', 'seo_title', 'seo_description',
        'canonical_url', 'og_title', 'og_description', 'og_media_id', 'robots_directive',
    ];

    protected function casts(): array
    {
        return ['start_at' => 'datetime', 'end_at' => 'datetime', 'featured' => 'boolean', 'sort_order' => 'integer'];
    }

    protected static function booted(): void
    {
        static::saving(function (Promotion $promotion): void {
            $promotion->slug = Str::slug($promotion->slug ?: $promotion->title);
            Validator::make($promotion->only(['start_at', 'end_at']), [
                'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            ])->validate();
        });
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->where(fn (Builder $query) => $query->whereNull('start_at')->orWhere('start_at', '<=', now()))
            ->where(fn (Builder $query) => $query->whereNull('end_at')->orWhere('end_at', '>=', now()));
    }

    public function banner(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'banner_media_id');
    }

    public function ogMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'og_media_id');
    }
}
