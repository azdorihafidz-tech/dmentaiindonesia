<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WebImageOptimizer
{
    /** @return array{path: string, mime_type: string, file_size: int}|null */
    public function optimize(string $diskName, string $path, int $maxDimension = 1600, int $quality = 88): ?array
    {
        $disk = Storage::disk($diskName);

        if (! $disk->exists($path) || ! function_exists('imagewebp')) {
            return null;
        }

        $absolutePath = $disk->path($path);
        $imageInfo = @getimagesize($absolutePath);
        $originalSize = @filesize($absolutePath);

        if (! is_array($imageInfo) || ! is_int($originalSize)) {
            return null;
        }

        $mime = $imageInfo['mime'] ?? '';
        if (! in_array($mime, ['image/png', 'image/jpeg', 'image/webp'], true)) {
            return null;
        }

        if ($mime === 'image/webp' && $originalSize <= 768 * 1024) {
            return null;
        }

        $width = (int) ($imageInfo[0] ?? 0);
        $height = (int) ($imageInfo[1] ?? 0);
        if ($width < 1 || $height < 1 || $width * $height > 25000000) {
            return null;
        }

        $source = match ($mime) {
            'image/png' => function_exists('imagecreatefrompng') ? @imagecreatefrompng($absolutePath) : false,
            'image/jpeg' => function_exists('imagecreatefromjpeg') ? @imagecreatefromjpeg($absolutePath) : false,
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($absolutePath) : false,
            default => false,
        };

        if (! $source instanceof \GdImage) {
            return null;
        }

        $scale = min(1, $maxDimension / max($width, $height));
        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));
        $image = @imagecreatetruecolor($targetWidth, $targetHeight);

        if (! $image instanceof \GdImage) {
            imagedestroy($source);

            return null;
        }

        $hasAlpha = in_array($mime, ['image/png', 'image/webp'], true);
        if ($hasAlpha) {
            imagealphablending($image, false);
            imagesavealpha($image, true);
            $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
            imagefill($image, 0, 0, $transparent);
        } else {
            $white = imagecolorallocate($image, 255, 255, 255);
            imagefill($image, 0, 0, $white);
        }

        imagecopyresampled($image, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
        imagedestroy($source);

        $optimizedPath = dirname($path).'/'.Str::random(40).'.webp';
        $optimizedAbsolutePath = $disk->path($optimizedPath);
        $saved = @imagewebp($image, $optimizedAbsolutePath, $quality);
        imagedestroy($image);

        if (! $saved || ! is_file($optimizedAbsolutePath)) {
            $disk->delete($optimizedPath);

            return null;
        }

        clearstatcache(true, $optimizedAbsolutePath);
        $optimizedSize = filesize($optimizedAbsolutePath);
        $optimizedInfo = @getimagesize($optimizedAbsolutePath);

        if (! is_int($optimizedSize) || $optimizedSize >= $originalSize || ($optimizedInfo['mime'] ?? null) !== 'image/webp') {
            $disk->delete($optimizedPath);

            return null;
        }

        $disk->delete($path);

        return [
            'path' => $optimizedPath,
            'mime_type' => 'image/webp',
            'file_size' => $optimizedSize,
        ];
    }
}
