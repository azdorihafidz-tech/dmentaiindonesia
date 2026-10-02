<?php

namespace App\Filament\Pages;

use App\Models\Media;
use App\Services\BrandAssetManager;
use App\Services\SiteSettings as Settings;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class WebsiteSettings extends Page
{
    protected string $view = 'filament.pages.website-settings';

    protected static string|\UnitEnum|null $navigationGroup = 'Website';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Pengaturan website';

    protected static ?string $title = 'Pengaturan website';

    public ?array $data = [];

    public function mount(): void
    {
        $this->fillSettingsForm();
    }

    private function fillSettingsForm(): void
    {
        $defaults = [
            'brand' => ['site_name' => "D'Mentai Indonesia", 'tagline' => 'Dimsum & Gyoza'],
            'seo' => ['default_title' => "D'Mentai Indonesia | Dimsum & Gyoza"],
        ];

        $data = array_replace_recursive($defaults, app(Settings::class)->formData());

        foreach ($this->assetFields() as [$group, $mediaField, $uploadField, $type]) {
            $media = Media::query()->find($data[$group][$mediaField] ?? null);
            $data[$group][$uploadField] = $media?->path;
        }

        $this->form->fill($data);
    }

    /** @return list<array{string, string, string, string}> */
    private function assetFields(): array
    {
        return [
            ['brand', 'logo_media_id', 'logo_upload', 'logo'],
            ['brand', 'favicon_media_id', 'favicon_upload', 'favicon'],
            ['seo', 'default_og_media_id', 'default_og_upload', 'og_image'],
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Pengaturan')->tabs([
                Tab::make('Brand')->schema([
                    Section::make('Identitas brand')->schema([
                        TextInput::make('brand.site_name')->label('Nama website')->required()->maxLength(120),
                        Textarea::make('brand.description')->label('Deskripsi bisnis')->rows(3)->maxLength(500),
                        TextInput::make('brand.tagline')->label('Tagline')->maxLength(180),
                    ])->columns(2),
                    Section::make('BRAND ASSETS')->schema([
                        FileUpload::make('brand.logo_upload')->label('Logo')->image()->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])
                            ->disk('public')->directory('brand-assets/logo')->visibility('public')->maxSize(5120)->imagePreviewHeight('120')
                            ->helperText('Logo utama yang digunakan pada header website. PNG, JPG, atau WebP; maksimal 5 MB. Lebar sekitar 300 px atau lebih disarankan, dengan latar transparan bila tersedia.')
                            ->deleteUploadedFileUsing(fn (string $file) => app(BrandAssetManager::class)->deleteIfUnregistered($file))
                            ->afterStateUpdated(fn (Set $set, mixed $state) => $state === null ? $set('brand.logo_media_id', null) : null),
                        Select::make('brand.logo_media_id')->label('Pilih logo dari Media Library')->options(fn (): array => app(BrandAssetManager::class)->options('logo'))
                            ->searchable()->preload()->nullable()->placeholder('Pilih aset dari folder Brand')
                            ->afterStateUpdated(fn (Set $set, mixed $state) => $set('brand.logo_upload', app(BrandAssetManager::class)->findBrandAsset(filled($state) ? (int) $state : null, 'logo')?->path)),
                        FileUpload::make('brand.favicon_upload')->label('Favicon')->acceptedFileTypes(['image/png', 'image/x-icon', 'image/vnd.microsoft.icon'])
                            ->disk('public')->directory('brand-assets/favicon')->visibility('public')->maxSize(1024)->imagePreviewHeight('64')
                            ->helperText('Icon yang digunakan pada tab browser. PNG atau ICO; maksimal 1 MB. Ukuran 32 x 32, 48 x 48, atau 512 x 512 disarankan.')
                            ->deleteUploadedFileUsing(fn (string $file) => app(BrandAssetManager::class)->deleteIfUnregistered($file))
                            ->afterStateUpdated(fn (Set $set, mixed $state) => $state === null ? $set('brand.favicon_media_id', null) : null),
                        Select::make('brand.favicon_media_id')->label('Pilih favicon dari Media Library')->options(fn (): array => app(BrandAssetManager::class)->options('favicon'))
                            ->searchable()->preload()->nullable()->placeholder('Pilih aset dari folder Brand')
                            ->afterStateUpdated(fn (Set $set, mixed $state) => $set('brand.favicon_upload', app(BrandAssetManager::class)->findBrandAsset(filled($state) ? (int) $state : null, 'favicon')?->path)),
                    ])->columns(2),
                ]),
                Tab::make('Kontak')->schema([Section::make('Kontak dan lokasi')->schema([
                    TextInput::make('contact.email')->label('Email')->email()->maxLength(255),
                    TextInput::make('contact.phone')->label('Telepon')->tel()->maxLength(40),
                    TextInput::make('contact.whatsapp')->label('WhatsApp')->tel()->maxLength(40),
                    Textarea::make('location.address')->label('Alamat')->rows(3),
                    TextInput::make('location.city')->label('Kota')->maxLength(120),
                    TextInput::make('location.province')->label('Provinsi')->maxLength(120),
                    TextInput::make('location.country')->label('Negara')->maxLength(120),
                    TextInput::make('location.maps_url')->label('Google Maps URL')->url()->maxLength(2048),
                    Textarea::make('location.business_hours')->label('Jam operasional')->rows(4),
                ])->columns(2)]),
                Tab::make('Sosial')->schema([Section::make('Media sosial')->schema([
                    TextInput::make('social.instagram')->label('Instagram')->url()->maxLength(2048),
                    TextInput::make('social.tiktok')->label('TikTok')->url()->maxLength(2048),
                    TextInput::make('social.facebook')->label('Facebook')->url()->maxLength(2048),
                    TextInput::make('social.youtube')->label('YouTube')->url()->maxLength(2048),
                ])->columns(2)]),
                Tab::make('Footer & integrasi')->schema([
                    Section::make('Footer')->schema([
                        Textarea::make('footer.text')->label('Teks footer')->rows(3),
                        TextInput::make('footer.copyright')->label('Hak cipta')->maxLength(255),
                    ])->columns(2),
                    Section::make('Integrasi')->schema([
                        TextInput::make('analytics.google_analytics_id')->label('Google Analytics Measurement ID')->regex('/^G-[A-Z0-9]+$/i')->maxLength(32),
                        TextInput::make('analytics.search_console_verification')->label('Search Console verification')->maxLength(255),
                    ])->columns(2),
                ]),
                Tab::make('SEO')->schema([Section::make('SEO default')->schema([
                    TextInput::make('seo.default_title')->label('Judul default')->maxLength(255),
                    Textarea::make('seo.meta_description')->label('Deskripsi default')->rows(3)->maxLength(320),
                    TextInput::make('seo.og_title')->label('Judul Open Graph default')->maxLength(255),
                    Textarea::make('seo.og_description')->label('Deskripsi Open Graph default')->rows(3)->maxLength(320),
                    FileUpload::make('seo.default_og_upload')->label('Default OG Image')->image()->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])
                        ->disk('public')->directory('brand-assets/og_image')->visibility('public')->maxSize(5120)->imagePreviewHeight('160')
                        ->helperText('Gambar default yang digunakan ketika halaman tidak memiliki OG image khusus. PNG, JPG, atau WebP; maksimal 5 MB. Rasio 1200 x 630 disarankan.')
                        ->deleteUploadedFileUsing(fn (string $file) => app(BrandAssetManager::class)->deleteIfUnregistered($file))
                        ->afterStateUpdated(fn (Set $set, mixed $state) => $state === null ? $set('seo.default_og_media_id', null) : null),
                    Select::make('seo.default_og_media_id')->label('Pilih gambar dari Media Library')->options(fn (): array => app(BrandAssetManager::class)->options('og_image'))
                        ->searchable()->preload()->nullable()->placeholder('Pilih aset dari folder Brand')
                        ->afterStateUpdated(fn (Set $set, mixed $state) => $set('seo.default_og_upload', app(BrandAssetManager::class)->findBrandAsset(filled($state) ? (int) $state : null, 'og_image')?->path)),
                ])->columns(2)]),
            ])->columnSpanFull(),
        ])->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $assets = app(BrandAssetManager::class);

        foreach ($this->assetFields() as [$group, $mediaField, $uploadField, $type]) {
            $path = $data[$group][$uploadField] ?? null;
            $selectedId = $data[$group][$mediaField] ?? null;
            $selectedMedia = filled($selectedId) ? Media::query()->find((int) $selectedId) : null;

            if (filled($path)) {
                $registeredMedia = $assets->findByPath((string) $path, $type);
                $media = $selectedMedia && $registeredMedia && $selectedMedia->is($registeredMedia)
                    ? $registeredMedia
                    : $assets->registerUpload((string) $path, $type);
                $data[$group][$mediaField] = (string) $media->id;
            } else {
                $data[$group][$mediaField] = null;
            }

            unset($data[$group][$uploadField]);
        }

        app(Settings::class)->save($data);
        $this->fillSettingsForm();
        Notification::make()->success()->title('Pengaturan disimpan')->send();
    }
}
