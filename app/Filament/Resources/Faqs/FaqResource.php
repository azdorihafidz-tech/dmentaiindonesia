<?php

namespace App\Filament\Resources\Faqs;

use App\Filament\Resources\Faqs\Pages\ManageFaqs;
use App\Models\Faq;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FaqResource extends Resource
{
    protected static ?string $model = Faq::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static string|\UnitEnum|null $navigationGroup = 'Engagement';

    protected static ?string $navigationLabel = 'FAQ';

    protected static ?string $modelLabel = 'FAQ';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([Section::make('Tanya jawab')->schema([
            TextInput::make('question')->label('Pertanyaan')->required()->maxLength(255),
            TextInput::make('category')->label('Kategori')->maxLength(100),
            Textarea::make('answer')->label('Jawaban')->required()->rows(5)->maxLength(10000)->columnSpanFull(),
            Select::make('status')->options(['active' => 'Aktif', 'inactive' => 'Nonaktif'])->default('inactive')->required(),
            TextInput::make('sort_order')->label('Urutan')->numeric()->integer()->minValue(0)->default(0)->required(),
        ])->columns(2)]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('question')->label('Pertanyaan')->searchable()->sortable(),
            TextColumn::make('category')->label('Kategori')->badge(),
            TextColumn::make('answer')->label('Jawaban')->limit(90)->searchable(),
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
        return ['index' => ManageFaqs::route('/')];
    }
}
