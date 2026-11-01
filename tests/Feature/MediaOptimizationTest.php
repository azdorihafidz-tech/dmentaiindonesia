<?php

namespace Tests\Feature;

use App\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaOptimizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_uploaded_pngs_are_resized_and_optimized_to_webp_without_losing_transparency(): void
    {
        if (! function_exists('imagewebp')) {
            $this->markTestSkipped('GD WebP support is unavailable.');
        }

        Storage::fake('public');
        $disk = Storage::disk('public');
        $disk->makeDirectory('media');
        $sourcePath = $disk->path('media/noisy.png');
        $image = imagecreatetruecolor(640, 640);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        mt_srand(42);

        for ($y = 0; $y < 640; $y++) {
            for ($x = 0; $x < 640; $x++) {
                $alpha = $x < 80 && $y < 80 ? 127 : 0;
                $color = imagecolorallocatealpha($image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255), $alpha);
                imagesetpixel($image, $x, $y, $color);
            }
        }

        imagepng($image, $sourcePath, 6);
        imagedestroy($image);
        $originalSize = $disk->size('media/noisy.png');

        $media = Media::query()->create([
            'disk' => 'public',
            'path' => 'media/noisy.png',
            'original_name' => 'noisy.png',
            'mime_type' => 'image/png',
            'file_size' => $originalSize,
            'folder' => 'general',
        ]);

        $this->assertSame('image/webp', $media->mime_type);
        $this->assertStringEndsWith('.webp', $media->path);
        $this->assertLessThan($originalSize, $media->file_size);
        $disk->assertMissing('media/noisy.png');
        $disk->assertExists($media->path);

        $optimized = imagecreatefromwebp($disk->path($media->path));
        $transparentPixel = imagecolorat($optimized, 1, 1);
        $this->assertSame(127, ($transparentPixel >> 24) & 0x7f);
        imagedestroy($optimized);
    }
}
