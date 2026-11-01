<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Services\WebImageOptimizer;

class Media extends Model
{
    protected $table = 'media';

    protected $fillable = [
        'disk',
        'path',
        'original_name',
        'mime_type',
        'file_size',
        'alt_text',
        'caption',
        'folder',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Media $media): void {
            if ($media->disk !== 'public' || ! str_starts_with((string) $media->path, 'media/')) {
                return;
            }

            $optimized = app(WebImageOptimizer::class)->optimize($media->disk, $media->path);
            if ($optimized) {
                $media->path = $optimized['path'];
                $media->mime_type = $optimized['mime_type'];
                $media->file_size = $optimized['file_size'];
            }
        });

        static::deleting(function (Media $media): void {
            Storage::disk($media->disk)->delete($media->path);
        });
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function getFormattedFileSizeAttribute(): string
    {
        return number_format($this->file_size / 1024, 1).' KB';
    }
}
