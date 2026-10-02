<?php

namespace App\Filament\Resources\Promotions;

use App\Filament\Resources\Promotions\Pages\ManagePromotions;
use App\Models\Promotion;
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
use Filament\Forms\Components\Toggle;
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

class PromotionResource extends Resource
{
    protected static ?string $model = Promotion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static string|\UnitEnum|null $navigationGroup = 'Pemasaran';

    protected static ?string $navigationLabel = 'Promosi';

    protected static ?string $modelLabel = 'promosi';

    public static function form(Schema $schema): Schema
    {
        $images = fn (): array => app(SiteSettings::class)->imageOptions();

        return $schema->components([
            Tabs::make('Promosi')->tabs([
                Tab::make('Konten')->schema([
                    Section::make('Informasi promosi')->schema([
                        TextInput::make('title')->label('Judul')->required()->maxLength(255)->live(onBlur: true)
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
                        Select::make('banner_media_id')->label('Banner')->options($images)->searchable()->preload(),
                    ])->columns(2),
                    Section::make('Tombol dan jadwal')->schema([
                        TextInput::make('cta_text')->label('Teks tombol')->maxLength(80),
                        TextInput::make('cta_url')->label('URL tombol')->url()->maxLength(2048),
                        DateTimePicker::make('start_at')->label('Mulai')->seconds(false),
                        DateTimePicker::make('end_at')->label('Berakhir')->seconds(false)->afterOrEqual('start_at'),
                        Select::make('status')->options(['active' => 'Aktif', 'inactive' => 'Nonaktif'])->default('inactive')->required(),
                        Toggle::make('featured')->label('Promosi unggulan')->default(false),
                        TextInput::make('sort_order')->label('Urutan')->numeric()->integer()->minValue(0)->default(0)->required(),
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
            ImageColumn::make('banner.path')->disk('public')->label('Banner'),
            TextColumn::make('title')->label('Judul')->searchable()->sortable(),
            TextColumn::make('start_at')->label('Mulai')->dateTime()->sortable(),
            TextColumn::make('end_at')->label('Berakhir')->dateTime()->sortable(),
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
        return ['index' => ManagePromotions::route('/')];
    }
}
