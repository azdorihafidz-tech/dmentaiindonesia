<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\Pages\Pages\ManagePages;
use App\Models\Page;
use App\Services\SiteSettings;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Website';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Halaman';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Konten halaman')->tabs([
                Tab::make('Konten')->schema([
                    Section::make()->schema([
                        TextInput::make('title')->label('Judul')->required()->maxLength(255)
                            ->live(onBlur: true)->afterStateUpdated(function (?string $state, callable $set, callable $get): void {
                                if (blank($get('slug'))) {
                                    $set('slug', Str::slug((string) $state));
                                }
                            }),
                        TextInput::make('slug')->label('Slug')->required()->maxLength(255)->unique(ignoreRecord: true)
                            ->rule('regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/')->helperText('URL publik: /{slug}.'),
                        Textarea::make('excerpt')->label('Ringkasan')->rows(3)->maxLength(500)->columnSpanFull(),
                        RichEditor::make('content')->label('Konten')->toolbarButtons([
                            ['bold', 'italic', 'underline', 'strike', 'link'],
                            ['h2', 'h3'],
                            ['bulletList', 'orderedList', 'blockquote'],
                            ['undo', 'redo'],
                        ])->columnSpanFull(),
                        Select::make('featured_media_id')->label('Gambar utama')->options(fn (): array => app(SiteSettings::class)->imageOptions())->searchable()->preload(),
                    ])->columns(2),
                    Section::make('Publikasi')->schema([
                        Select::make('status')->label('Status')->options(['draft' => 'Draft', 'published' => 'Terbit'])->default('draft')->required(),
                        DateTimePicker::make('published_at')->label('Waktu terbit')->seconds(false),
                    ])->columns(2),
                ]),
                Tab::make('SEO')->schema([
                    Section::make('Metadata pencarian')->schema([
                        TextInput::make('seo_title')->label('Judul SEO')->maxLength(255),
                        Textarea::make('seo_description')->label('Deskripsi SEO')->rows(3)->maxLength(320),
                        TextInput::make('canonical_url')->label('Canonical URL')->url()->maxLength(2048),
                        Select::make('robots_directive')->label('Robots')->options([
                            'index,follow' => 'Index, follow', 'noindex,follow' => 'Noindex, follow', 'noindex,nofollow' => 'Noindex, nofollow',
                        ]),
                    ])->columns(2),
                    Section::make('Open Graph')->schema([
                        TextInput::make('og_title')->label('Judul Open Graph')->maxLength(255),
                        Textarea::make('og_description')->label('Deskripsi Open Graph')->rows(3)->maxLength(320),
                        Select::make('og_media_id')->label('Gambar Open Graph')->options(fn (): array => app(SiteSettings::class)->imageOptions())->searchable()->preload(),
                    ])->columns(2),
                ]),
            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title')->label('Judul')->searchable()->sortable(),
            TextColumn::make('slug')->label('Slug')->searchable(),
            TextColumn::make('status')->label('Status')->badge()->color(fn (string $state): string => $state === 'published' ? 'success' : 'gray'),
            TextColumn::make('published_at')->label('Waktu terbit')->dateTime()->sortable(),
            TextColumn::make('updated_at')->label('Diubah')->dateTime()->sortable(),
        ])->filters([
            SelectFilter::make('status')->options(['draft' => 'Draft', 'published' => 'Terbit']),
        ])->recordActions([EditAction::make(), DeleteAction::make()])->toolbarActions([
            BulkActionGroup::make([DeleteBulkAction::make()]),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManagePages::route('/')];
    }
}
