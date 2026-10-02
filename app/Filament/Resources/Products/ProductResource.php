<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages\ManageProducts;
use App\Models\Product;
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

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCake;

    protected static string|\UnitEnum|null $navigationGroup = 'Katalog';

    protected static ?string $navigationLabel = 'Produk';

    protected static ?string $modelLabel = 'produk';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Produk')->tabs([
                Tab::make('Informasi')->schema([
                    Section::make('Detail produk')->schema([
                        Select::make('category_id')->label('Kategori')->relationship('category', 'name')->searchable()->preload()->required(),
                        TextInput::make('name')->label('Nama produk')->required()->maxLength(255)->live(onBlur: true)
                            ->afterStateUpdated(function (?string $state, callable $set, callable $get): void {
                                if (blank($get('slug'))) {
                                    $set('slug', Str::slug((string) $state));
                                }
                            }),
                        TextInput::make('slug')->required()->maxLength(255)->unique(ignoreRecord: true)->rule('regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'),
                        TextInput::make('short_description')->label('Deskripsi singkat')->maxLength(500)->columnSpanFull(),
                        RichEditor::make('description')->label('Deskripsi')->toolbarButtons([
                            ['bold', 'italic', 'link'], ['h2', 'h3'], ['bulletList', 'orderedList'], ['undo', 'redo'],
                        ])->columnSpanFull(),
                        Select::make('image_media_id')->label('Gambar produk')->options(fn (): array => app(SiteSettings::class)->imageOptions())->searchable()->preload(),
                    ])->columns(2),
                    Section::make('Harga dan tampilan')->schema([
                        TextInput::make('price')->label('Harga')->numeric()->minValue(0)->step(0.01)->prefix('Rp')->nullable()->helperText('Kosongkan jika harga belum ditentukan.'),
                        TextInput::make('promotional_price')->label('Harga promo')->numeric()->minValue(0)->step(0.01)->prefix('Rp')->lte('price')->nullable()
                            ->helperText('Harga promo harus sama dengan atau lebih rendah dari harga normal.'),
                        Select::make('status')->options(['active' => 'Aktif', 'inactive' => 'Nonaktif'])->default('inactive')->required(),
                        TextInput::make('sort_order')->label('Urutan')->numeric()->integer()->minValue(0)->default(0)->required(),
                        Toggle::make('featured')->label('Produk unggulan')->default(false),
                    ])->columns(2),
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
                    Select::make('og_media_id')->label('Gambar Open Graph')->options(fn (): array => app(SiteSettings::class)->imageOptions())->searchable()->preload(),
                ])->columns(2)]),
            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            ImageColumn::make('image.path')->disk('public')->label('Gambar'),
            TextColumn::make('name')->label('Produk')->searchable()->sortable(),
            TextColumn::make('category.name')->label('Kategori')->sortable(),
            TextColumn::make('price')->money('IDR')->placeholder('Belum ditentukan')->sortable(),
            TextColumn::make('status')->badge()->color(fn (string $state): string => $state === 'active' ? 'success' : 'gray'),
            IconColumn::make('featured')->boolean()->label('Unggulan'),
        ])->filters([
            SelectFilter::make('status')->options(['active' => 'Aktif', 'inactive' => 'Nonaktif']),
            SelectFilter::make('category')->relationship('category', 'name'),
        ])->recordActions([EditAction::make(), DeleteAction::make()])->toolbarActions([
            BulkActionGroup::make([DeleteBulkAction::make()]),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageProducts::route('/')];
    }
}
