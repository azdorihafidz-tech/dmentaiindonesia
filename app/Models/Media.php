<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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
