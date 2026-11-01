<?php

namespace App\Filament\Pages;

use App\Models\PageView;
use App\Services\CountryPresentation;
use BackedEnum;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class WebsiteVisitors extends Page implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    protected string $view = 'filament.pages.website-visitors';

    protected static string|\UnitEnum|null $navigationGroup = 'Laporan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEye;

    protected static ?string $navigationLabel = 'Negara pengunjung';

    protected static ?string $title = 'Statistik pengunjung';

    protected static ?int $navigationSort = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(PageView::query()
                ->selectRaw('MIN(id) as id, country_code, country_name, COUNT(DISTINCT visitor_hash) as unique_visitors, COUNT(*) as page_views')
                ->where('viewed_at', '>=', now()->subDays(30))
                ->groupBy('country_code', 'country_name'))
            ->heading('Pengunjung per negara (30 hari)')
            ->description('Negara diperkirakan dari database GeoLite2 lokal. Alamat IP digunakan hanya untuk pencarian dan tidak disimpan.')
            ->columns([
                TextColumn::make('country_code')
                    ->label('Negara')
                    ->placeholder('Belum diketahui')
                    ->formatStateUsing(fn (?string $state, PageView $record): string => CountryPresentation::label($state, $record->country_name)),
                TextColumn::make('unique_visitors')->label('Pengunjung unik')->numeric()->sortable(),
                TextColumn::make('page_views')->label('Kunjungan halaman')->numeric()->sortable(),
            ])
            ->defaultSort('unique_visitors', 'desc')
            ->paginated([25, 50, 100])
            ->emptyStateHeading('Belum ada data pengunjung')
            ->emptyStateDescription('Data negara akan muncul setelah database GeoLite2 dipasang dan ada kunjungan baru.');
    }
}
