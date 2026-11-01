<?php

namespace App\Filament\Resources\Media;

use App\Filament\Resources\Media\Pages\ManageMedia;
use App\Models\Media;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MediaResource extends Resource
{
    protected static ?string $model = Media::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Website';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Media';

    protected static ?string $modelLabel = 'media';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Berkas gambar')->schema([
                FileUpload::make('path')->label('Gambar')->disk('public')->directory('media')->visibility('public')
                    ->image()->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                    ->maxSize(5120)->storeFileNamesIn('original_name')->required(fn (?Media $record): bool => $record === null)
                    ->visible(fn (?Media $record): bool => $record === null)
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->helperText('JPG, PNG, WebP, atau GIF. Maksimal 5 MB. JPG, PNG, dan WebP dioptimalkan saat disimpan; GIF disimpan apa adanya.'),
                TextInput::make('alt_text')->label('Teks alternatif')->maxLength(255),
                TextInput::make('caption')->label('Keterangan')->maxLength(255),
                Select::make('folder')->label('Folder')->options(['general' => 'Umum', 'brand' => 'Brand', 'pages' => 'Halaman', 'seo' => 'SEO'])->default('general')->required(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            ImageColumn::make('path')->disk('public')->label('Pratinjau'),
            TextColumn::make('original_name')->label('Nama berkas')->searchable()->sortable(),
            TextColumn::make('mime_type')->label('Tipe'),
            TextColumn::make('formatted_file_size')->label('Ukuran'),
            TextColumn::make('folder')->label('Folder')->badge(),
            TextColumn::make('created_at')->label('Diunggah')->dateTime()->sortable(),
        ])->filters([
            SelectFilter::make('folder')->options(['general' => 'Umum', 'brand' => 'Brand', 'pages' => 'Halaman', 'seo' => 'SEO']),
        ])->recordActions([EditAction::make(), DeleteAction::make()])->toolbarActions([
            BulkActionGroup::make([DeleteBulkAction::make()]),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageMedia::route('/')];
    }
}
