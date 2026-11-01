<?php

namespace App\Filament\Widgets;

use App\Models\Article;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\Promotion;
use App\Services\PageViewMetrics;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class WebsiteStatsWidget extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected ?string $heading = 'Ringkasan website';

    protected ?string $description = 'Pengunjung dihitung dari sesi browser unik; alamat IP tidak disimpan.';

    protected function getStats(): array
    {
        $metrics = app(PageViewMetrics::class);

        return [
            Stat::make('Pengunjung hari ini', $metrics->uniqueVisitorsSince(now()->startOfDay()))
                ->description('Sesi unik hari ini')
                ->descriptionIcon(Heroicon::OutlinedUsers),
            Stat::make('Pengunjung minggu ini', $metrics->uniqueVisitorsSince(now()->startOfWeek()))
                ->description('Sesi unik sejak awal minggu')
                ->descriptionIcon(Heroicon::OutlinedUsers),
            Stat::make('Pengunjung bulan ini', $metrics->uniqueVisitorsSince(now()->startOfMonth()))
                ->description('Sesi unik sejak awal bulan')
                ->descriptionIcon(Heroicon::OutlinedUsers),
            Stat::make('Negara pengunjung', $metrics->countryCount())
                ->description('Negara yang tercatat sejak mulai tracking')
                ->descriptionIcon(Heroicon::OutlinedGlobeAlt),
            Stat::make('Produk aktif', Product::available()->count())
                ->description('Menu yang tampil di website')
                ->descriptionIcon(Heroicon::OutlinedShoppingBag),
            Stat::make('Promosi aktif', Promotion::available()->count())
                ->description('Promosi yang sedang tersedia')
                ->descriptionIcon(Heroicon::OutlinedTag),
            Stat::make('Outlet aktif', Outlet::active()->count())
                ->description('Lokasi yang tampil di website')
                ->descriptionIcon(Heroicon::OutlinedMapPin),
            Stat::make('Artikel terbit', Article::published()->count())
                ->description('Artikel yang dapat dibaca')
                ->descriptionIcon(Heroicon::OutlinedDocumentText),
        ];
    }
}
