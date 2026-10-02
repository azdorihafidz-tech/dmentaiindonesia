<?php

namespace App\Services;

use App\Models\Media;
use App\Models\SiteSetting;

class SiteSettings
{
    /** @var array<string, string|null>|null */
    private ?array $values = null;

    /** @var array<int, string>|null */
    private ?array $imageOptions = null;

    /** @return array<string, string|null> */
    public function all(): array
    {
        return $this->values ??= SiteSetting::query()->pluck('value', 'key')->all();
    }

    public function get(string $key, ?string $default = null): ?string
    {
        return $this->all()[$key] ?? $default;
    }

    public function media(string $key): ?Media
    {
        $id = $this->get($key);

        return filled($id) ? Media::query()->find((int) $id) : null;
    }

    /** @return array<int, string> */
    public function imageOptions(): array
    {
        return $this->imageOptions ??= Media::query()
            ->whereIn('mime_type', ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml'])
            ->orderBy('original_name')
            ->pluck('original_name', 'id')
            ->all();
    }

    /** @return array<string, array<string, string|null>> */
    public function formData(): array
    {
        $data = [];

        foreach ($this->all() as $key => $value) {
            [$group, $name] = array_pad(explode('.', $key, 2), 2, null);

            if ($name !== null) {
                $data[$group][$name] = $value;
            }
        }

        return $data;
    }

    /** @param array<string, array<string, mixed>> $data */
    public function save(array $data): void
    {
        $now = now();
        $rows = [];

        foreach ($data as $group => $settings) {
            foreach ($settings as $name => $value) {
                if (is_array($value) || is_object($value)) {
                    continue;
                }

                $rows[] = [
                    'group' => $group,
                    'key' => $group.'.'.$name,
                    'value' => filled($value) ? (string) $value : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if ($rows !== []) {
            SiteSetting::query()->upsert($rows, ['key'], ['group', 'value', 'updated_at']);
        }

        $this->values = null;
        $this->imageOptions = null;
    }
}
