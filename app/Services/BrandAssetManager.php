<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class BrandAssetManager
{
    /** @var array<string, array{mimes: list<string>, extensions: list<string>, max_kb: int, label: string}> */
    private const TYPES = [
        'logo' => [
            'mimes' => ['image/png', 'image/jpeg', 'image/webp'],
            'extensions' => ['png', 'jpg', 'jpeg', 'webp'],
            'max_kb' => 5120,
            'label' => 'Logo website',
        ],
        'favicon' => [
            'mimes' => ['image/png', 'image/x-icon', 'image/vnd.microsoft.icon'],
            'extensions' => ['png', 'ico'],
            'max_kb' => 1024,
            'label' => 'Favicon website',
        ],
        'og_image' => [
            'mimes' => ['image/png', 'image/jpeg', 'image/webp'],
            'extensions' => ['png', 'jpg', 'jpeg', 'webp'],
            'max_kb' => 5120,
            'label' => 'Default Open Graph image',
        ],
    ];

    /** @return array<int, string> */
    public function options(string $type): array
    {
        $config = $this->configFor($type);

        return Media::query()
            ->where('folder', 'brand')
            ->whereIn('mime_type', $config['mimes'])
            ->orderBy('original_name')
            ->get(['id', 'disk', 'path', 'original_name'])
            ->filter(fn (Media $media): bool => Storage::disk($media->disk)->exists($media->path))
            ->pluck('original_name', 'id')
            ->all();
    }

    public function findBrandAsset(?int $id, string $type): ?Media
    {
        if (! $id) {
            return null;
        }

        $config = $this->configFor($type);

        $media = Media::query()
            ->whereKey($id)
            ->where('folder', 'brand')
            ->whereIn('mime_type', $config['mimes'])
            ->first();

        return $media && Storage::disk($media->disk)->exists($media->path) ? $media : null;
    }

    public function findByPath(string $path, string $type): ?Media
    {
        $config = $this->configFor($type);

        $media = Media::query()
            ->where('disk', 'public')
            ->where('path', $path)
            ->whereIn('mime_type', $config['mimes'])
            ->first();

        return $media && Storage::disk($media->disk)->exists($media->path) ? $media : null;
    }

    public function registerUpload(string $path, string $type): Media
    {
        $config = $this->configFor($type);
        $existing = $this->findByPath($path, $type);

        if ($existing) {
            return $existing;
        }

        $expectedDirectory = 'brand-assets/'.$type;
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (dirname($path) !== $expectedDirectory || basename($path) === '' || str_contains(basename($path), '..') || ! in_array($extension, $config['extensions'], true)) {
            $this->reject($type, 'Lokasi atau format file tidak diizinkan.');
        }

        $disk = Storage::disk('public');
        if (! $disk->exists($path)) {
            $this->reject($type, 'File yang diunggah tidak ditemukan.');
        }

        $absolutePath = $disk->path($path);
        $size = filesize($absolutePath);
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($absolutePath) ?: '';

        if ($extension === 'ico') {
            $header = file_get_contents($absolutePath, false, null, 0, 6);
            $validIconHeader = is_string($header)
                && strlen($header) === 6
                && substr($header, 0, 4) === "\x00\x00\x01\x00"
                && unpack('vcount', substr($header, 4, 2))['count'] > 0;

            if (! $validIconHeader) {
                $this->reject($type, 'File ICO tidak memiliki header icon yang valid.');
            }

            if (! in_array($mime, $config['mimes'], true)) {
                $mime = 'image/x-icon';
            }
        } else {
            $image = @getimagesize($absolutePath);
            if (! is_array($image) || ! in_array($image['mime'] ?? '', $config['mimes'], true)) {
                $this->reject($type, 'Isi file bukan gambar yang didukung.');
            }

            $mime = $image['mime'];
        }

        if (! in_array($mime, $config['mimes'], true)) {
            $this->reject($type, 'Tipe MIME file tidak diizinkan.');
        }

        if (! is_int($size) || $size < 1 || $size > $config['max_kb'] * 1024) {
            $this->reject($type, 'Ukuran file melewati batas yang diizinkan.');
        }

        $altText = match ($type) {
            'logo' => "Logo D'Mentai Indonesia",
            'favicon' => "Favicon D'Mentai Indonesia",
            default => "Default Open Graph image D'Mentai Indonesia",
        };

        return Media::updateOrCreate(
            ['disk' => 'public', 'path' => $path],
            [
                'original_name' => $config['label'].'.'.$extension,
                'mime_type' => $mime,
                'file_size' => $size,
                'alt_text' => $altText,
                'caption' => 'Aset brand yang diunggah melalui Pengaturan website.',
                'folder' => 'brand',
            ],
        );
    }

    public function deleteIfUnregistered(string $path): void
    {
        if (preg_match('~^brand-assets/(logo|favicon|og_image)/[A-Za-z0-9_-]+\.(png|jpe?g|webp|ico)$~i', $path) !== 1) {
            return;
        }

        if (! Media::query()->where('disk', 'public')->where('path', $path)->exists()) {
            Storage::disk('public')->delete($path);
        }
    }

    /** @return array{mimes: list<string>, extensions: list<string>, max_kb: int, label: string} */
    private function configFor(string $type): array
    {
        if (! isset(self::TYPES[$type])) {
            throw new \InvalidArgumentException('Unknown brand asset type.');
        }

        return self::TYPES[$type];
    }

    private function reject(string $type, string $message): never
    {
        $field = $type === 'og_image' ? 'seo.default_og_upload' : 'brand.'.$type.'_upload';

        throw ValidationException::withMessages(['data.'.$field => $message]);
    }
}
