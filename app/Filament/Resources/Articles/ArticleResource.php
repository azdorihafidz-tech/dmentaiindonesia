<?php

namespace App\Filament\Resources\Articles;

use App\Filament\Resources\Articles\Pages\ManageArticles;
use App\Models\Article;
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
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static string|\UnitEnum|null $navigationGroup = 'Konten';

    protected static ?string $navigationLabel = 'Artikel';

    protected static ?string $modelLabel = 'artikel';

    public static function form(Schema $schema): Schema
    {
        $images = fn (): array => app(SiteSettings::class)->imageOptions();

        return $schema->components([
            Tabs::make('Artikel')->tabs([
                Tab::make('Konten')->schema([
                    Section::make('Informasi artikel')->schema([
                        Select::make('category_id')->label('Kategori')->relationship('category', 'name')->searchable()->preload(),
                        TextInput::make('title')->label('Judul')->required()->maxLength(255)->live(onBlur: true)
                            ->afterStateUpdated(function (?string $state, callable $set, callable $get): void {
                                if (blank($get('slug'))) {
                                    $set('slug', Str::slug((string) $state));
                                }
                            }),
                        TextInput::make('slug')->required()->maxLength(255)->unique(ignoreRecord: true)->rule('regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'),
                        Textarea::make('excerpt')->label('Ringkasan')->rows(3)->maxLength(1000)->columnSpanFull(),
                        RichEditor::make('content')->label('Konten artikel')->toolbarButtons([
                            ['bold', 'italic', 'underline', 'strike', 'link'], ['h2', 'h3'],
                            ['bulletList', 'orderedList', 'blockquote'], ['undo', 'redo'],
                        ])->columnSpanFull(),
                        Select::make('featured_media_id')->label('Gambar utama')->options($images)->searchable()->preload(),
                        Select::make('author_id')->label('Penulis')->relationship('author', 'name')->searchable()->preload(),
                    ])->columns(2),
                    Section::make('Publikasi')->schema([
                        Select::make('status')->options(['draft' => 'Draft', 'published' => 'Terbit'])->default('draft')->required(),
                        DateTimePicker::make('published_at')->label('Waktu terbit')->seconds(false)
                            ->helperText('Jika dikosongkan saat diterbitkan, waktu saat ini digunakan.'),
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
                    Select::make('og_media_id')->label('Gambar Open Graph')->options($images)->searchable()->preload(),
                ])->columns(2)]),
            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            ImageColumn::make('featuredMedia.path')->disk('public')->label('Gambar'),
            TextColumn::make('title')->label('Judul')->searchable()->sortable(),
            TextColumn::make('category.name')->label('Kategori')->sortable(),
            TextColumn::make('status')->badge()->color(fn (string $state): string => $state === 'published' ? 'success' : 'gray'),
            TextColumn::make('published_at')->label('Terbit')->dateTime()->sortable(),
            TextColumn::make('updated_at')->label('Diubah')->dateTime()->sortable(),
        ])->filters([
            SelectFilter::make('status')->options(['draft' => 'Draft', 'published' => 'Terbit']),
            SelectFilter::make('category')->relationship('category', 'name'),
        ])->recordActions([EditAction::make(), DeleteAction::make()])->toolbarActions([
            BulkActionGroup::make([DeleteBulkAction::make()]),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageArticles::route('/')];
    }
}
