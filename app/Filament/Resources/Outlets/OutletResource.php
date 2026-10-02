<?php

namespace App\Filament\Resources\Outlets;

use App\Filament\Resources\Outlets\Pages\ManageOutlets;
use App\Models\Outlet;
use App\Services\SiteSettings;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class OutletResource extends Resource
{
    protected static ?string $model = Outlet::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static string|\UnitEnum|null $navigationGroup = 'Lokasi';

    protected static ?string $navigationLabel = 'Outlet';

    protected static ?string $modelLabel = 'outlet';

    public static function form(Schema $schema): Schema
    {
        $images = fn (): array => app(SiteSettings::class)->imageOptions();

        return $schema->components([
            Tabs::make('Outlet')->tabs([
                Tab::make('Informasi')->schema([
                    Section::make('Identitas outlet')->schema([
                        TextInput::make('name')->label('Nama outlet')->required()->maxLength(255)->live(onBlur: true)
                            ->afterStateUpdated(function (?string $state, callable $set, callable $get): void {
                                if (blank($get('slug'))) {
                                    $set('slug', Str::slug((string) $state));
                                }
                            }),
                        TextInput::make('slug')->required()->maxLength(255)->unique(ignoreRecord: true)->rule('regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'),
                        Textarea::make('address')->label('Alamat')->rows(3),
                        TextInput::make('city')->label('Kota')->maxLength(120),
                        TextInput::make('province')->label('Provinsi')->maxLength(120),
                        TextInput::make('country')->label('Negara')->maxLength(120),
                        TextInput::make('phone')->label('Telepon')->tel()->maxLength(40),
                        TextInput::make('whatsapp')->label('WhatsApp')->tel()->maxLength(40),
                        Textarea::make('business_hours')->label('Jam operasional')->rows(4),
                        TextInput::make('google_maps_url')->label('Google Maps URL')->url()->maxLength(2048),
                        TextInput::make('latitude')->label('Latitude')->numeric()->step(0.0000001)->minValue(-90)->maxValue(90)->rules(['required_with:longitude']),
                        TextInput::make('longitude')->label('Longitude')->numeric()->step(0.0000001)->minValue(-180)->maxValue(180)->rules(['required_with:latitude']),
                        Select::make('image_media_id')->label('Foto outlet')->options($images)->searchable()->preload(),
                        RichEditor::make('description')->label('Deskripsi')->toolbarButtons([
                            ['bold', 'italic', 'link'], ['h2', 'h3'], ['bulletList', 'orderedList'], ['undo', 'redo'],
                        ])->columnSpanFull(),
                    ])->columns(2),
                    Section::make('Tampilan')->schema([
                        Select::make('status')->options(['active' => 'Aktif', 'inactive' => 'Nonaktif'])->default('inactive')->required(),
                        Toggle::make('featured')->label('Outlet unggulan')->default(false),
                        TextInput::make('sort_order')->label('Urutan')->numeric()->integer()->minValue(0)->default(0)->required(),
                    ])->columns(3),
                ]),
                Tab::make('SEO')->schema([Section::make('Metadata SEO')->schema([
                    TextInput::make('seo_title')->label('Judul SEO')->maxLength(255),
                    Textarea::make('seo_description')->label('Deskripsi SEO')->rows(3)->maxLength(320),
                    TextInput::make('canonical_url')->label('Canonical URL')->url()->maxLength(2048),
                    Select::make('robots_directive')->label('Robots')->options([
                        'index,follow' => 'Index, follow', 'noindex,follow' => 'Noindex, follow', 'noindex,nofollow' => 'Noindex, nofollow',
                    ]),
                    TextInput::make('og_title')->label('Judul Open Graph')->maxLength(255),
                    Textarea::make('og_description')->label('Deskripsi Open Graph')->rows(3)->maxLength(320),
                    Select::make('og_media_id')->label('Gambar Open Graph')->options($images)->searchable()->preload(),
                ])->columns(2)]),
            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            ImageColumn::make('image.path')->disk('public')->label('Foto'),
            TextColumn::make('name')->label('Nama outlet')->searchable()->sortable(),
            TextColumn::make('address')->label('Alamat')->limit(50),
            TextColumn::make('status')->badge()->color(fn (string $state): string => $state === 'active' ? 'success' : 'gray'),
            IconColumn::make('featured')->boolean()->label('Unggulan'),
            TextColumn::make('sort_order')->label('Urutan')->sortable(),
        ])->defaultSort('sort_order')->filters([
            SelectFilter::make('status')->options(['active' => 'Aktif', 'inactive' => 'Nonaktif']),
        ])->recordActions([EditAction::make(), DeleteAction::make()])->toolbarActions([
            BulkActionGroup::make([DeleteBulkAction::make()]),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageOutlets::route('/')];
    }
}
