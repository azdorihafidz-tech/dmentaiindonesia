<?php

namespace App\Filament\Resources\Testimonials;

use App\Filament\Resources\Testimonials\Pages\ManageTestimonials;
use App\Models\Testimonial;
use App\Services\SiteSettings;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|\UnitEnum|null $navigationGroup = 'Pemasaran';

    protected static ?string $navigationLabel = 'Testimoni';

    protected static ?string $modelLabel = 'testimoni';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([Section::make('Testimoni pelanggan')->schema([
            TextInput::make('customer_name')->label('Nama pelanggan')->required()->maxLength(255),
            Select::make('image_media_id')->label('Foto pelanggan')->options(fn (): array => app(SiteSettings::class)->imageOptions())->searchable()->preload(),
            Textarea::make('content')->label('Isi testimoni')->required()->rows(5)->maxLength(5000)->columnSpanFull(),
            Select::make('status')->options(['active' => 'Aktif', 'inactive' => 'Nonaktif'])->default('inactive')->required(),
            Toggle::make('featured')->label('Testimoni unggulan')->default(false),
            TextInput::make('sort_order')->label('Urutan')->numeric()->integer()->minValue(0)->default(0)->required(),
        ])->columns(2)]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            ImageColumn::make('image.path')->disk('public')->label('Foto'),
            TextColumn::make('customer_name')->label('Nama')->searchable()->sortable(),
            TextColumn::make('content')->label('Testimoni')->limit(80)->searchable(),
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
        return ['index' => ManageTestimonials::route('/')];
    }
}
