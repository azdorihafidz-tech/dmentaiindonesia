<?php

namespace App\Filament\Pages;

use App\Services\SiteSettings as Settings;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
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
        $defaults = [
            'brand' => ['site_name' => "D'Mentai Indonesia", 'tagline' => 'Dimsum & Gyoza'],
            'seo' => ['default_title' => "D'Mentai Indonesia | Dimsum & Gyoza"],
        ];

        $this->form->fill(array_replace_recursive($defaults, app(Settings::class)->formData()));
    }

    public function form(Schema $schema): Schema
    {
        $images = fn (): array => app(Settings::class)->imageOptions();

        return $schema->components([
            Tabs::make('Pengaturan')->tabs([
                Tab::make('Brand')->schema([Section::make('Identitas brand')->schema([
                    TextInput::make('brand.site_name')->label('Nama website')->required()->maxLength(120),
                    Textarea::make('brand.description')->label('Deskripsi bisnis')->rows(3)->maxLength(500),
                    TextInput::make('brand.tagline')->label('Tagline')->maxLength(180),
                    Select::make('brand.logo_media_id')->label('Logo')->options($images)->searchable()->preload(),
                    Select::make('brand.favicon_media_id')->label('Favicon')->options($images)->searchable()->preload(),
                ])->columns(2)]),
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
                    Select::make('seo.default_og_media_id')->label('Gambar Open Graph dan sosial default')->options($images)->searchable()->preload(),
                ])->columns(2)]),
            ])->columnSpanFull(),
        ])->statePath('data');
    }

    public function save(): void
    {
        app(Settings::class)->save($this->form->getState());
        Notification::make()->success()->title('Pengaturan disimpan')->send();
    }
}
