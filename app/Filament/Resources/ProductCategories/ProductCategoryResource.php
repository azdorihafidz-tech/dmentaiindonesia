<?php

namespace App\Filament\Resources\ProductCategories;

use App\Filament\Resources\ProductCategories\Pages\ManageProductCategories;
use App\Models\ProductCategory;
use App\Services\SiteSettings;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductCategoryResource extends Resource
{
    protected static ?string $model = ProductCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|\UnitEnum|null $navigationGroup = 'Katalog';

    protected static ?string $navigationLabel = 'Kategori produk';

    protected static ?string $modelLabel = 'kategori produk';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([Section::make('Informasi kategori')->schema([
            TextInput::make('name')->label('Nama kategori')->required()->maxLength(255)->live(onBlur: true)
                ->afterStateUpdated(function (?string $state, callable $set, callable $get): void {
                    if (blank($get('slug'))) {
                        $set('slug', Str::slug((string) $state));
                    }
                }),
            TextInput::make('slug')->required()->maxLength(255)->unique(ignoreRecord: true)->rule('regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'),
            Textarea::make('description')->label('Deskripsi')->rows(3)->columnSpanFull(),
            Select::make('image_media_id')->label('Gambar kategori')->options(fn (): array => app(SiteSettings::class)->imageOptions())->searchable()->preload(),
            Select::make('status')->options(['active' => 'Aktif', 'inactive' => 'Nonaktif'])->default('inactive')->required(),
            TextInput::make('sort_order')->label('Urutan')->numeric()->integer()->minValue(0)->default(0)->required(),
        ])->columns(2)]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            ImageColumn::make('image.path')->disk('public')->label('Gambar'),
            TextColumn::make('name')->label('Nama')->searchable()->sortable(),
            TextColumn::make('slug')->searchable(),
            TextColumn::make('products_count')->counts('products')->label('Produk'),
            TextColumn::make('status')->badge()->color(fn (string $state): string => $state === 'active' ? 'success' : 'gray'),
            TextColumn::make('sort_order')->label('Urutan')->sortable(),
        ])->defaultSort('sort_order')->filters([
            SelectFilter::make('status')->options(['active' => 'Aktif', 'inactive' => 'Nonaktif']),
        ])->recordActions([EditAction::make(), DeleteAction::make()])->toolbarActions([
            BulkActionGroup::make([DeleteBulkAction::make()]),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageProductCategories::route('/')];
    }
}
