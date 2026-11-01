<?php

namespace App\Filament\Widgets;

use App\Models\PageView;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class PopularPagesWidget extends TableWidget
{
    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Halaman populer · 30 hari')
            ->query(
                PageView::query()
                    ->selectRaw('MIN(id) as id, path, COUNT(*) as views')
                    ->where('viewed_at', '>=', now()->subDays(30))
                    ->groupBy('path'),
            )
            ->columns([
                TextColumn::make('path')->label('Halaman')->searchable(),
                TextColumn::make('views')->label('Tayangan')->numeric()->sortable(),
            ])
            ->defaultSort('views', 'desc')
            ->emptyStateHeading('Belum ada tayangan')
            ->emptyStateDescription('Data kunjungan akan muncul setelah pengunjung membuka halaman publik.');
    }
}
