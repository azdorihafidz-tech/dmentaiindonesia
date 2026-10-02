<?php

namespace App\Filament\Resources\Gallery;

use App\Filament\Resources\Gallery\Pages\ManageGallery;
use App\Models\GalleryItem;
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

class GalleryResource extends Resource
{
    protected static ?string $model = GalleryItem::class;

    protected static ?string $slug = 'gallery';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|\UnitEnum|null $navigationGroup = 'Website';

    protected static ?string $navigationLabel = 'Galeri';

    protected static ?string $modelLabel = 'item galeri';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([Section::make('Foto galeri')->schema([
            TextInput::make('title')->label('Judul')->required()->maxLength(255),
            Select::make('image_media_id')->label('Gambar')->options(fn (): array => app(SiteSettings::class)->imageOptions())->searchable()->preload()->required(),
            Textarea::make('caption')->label('Keterangan')->rows(3),
            TextInput::make('alt_text')->label('Teks alternatif')->maxLength(255)->helperText('Jelaskan isi foto untuk aksesibilitas.'),
            TextInput::make('category')->label('Kategori')->maxLength(100),
            Select::make('status')->options(['active' => 'Aktif', 'inactive' => 'Nonaktif'])->default('inactive')->required(),
            TextInput::make('sort_order')->label('Urutan')->numeric()->integer()->minValue(0)->default(0)->required(),
        ])->columns(2)]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            ImageColumn::make('image.path')->disk('public')->label('Pratinjau'),
            TextColumn::make('title')->label('Judul')->searchable()->sortable(),
            TextColumn::make('category')->label('Kategori')->badge(),
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
        return ['index' => ManageGallery::route('/')];
    }
}
