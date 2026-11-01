<?php

namespace App\Services;

use App\Models\PageView;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class PageViewMetrics
{
    public function uniqueVisitorsSince(CarbonInterface $since): int
    {
        return PageView::query()
            ->where('viewed_at', '>=', $since)
            ->distinct()
            ->count('visitor_hash');
    }

    public function uniqueVisitors(): int
    {
        return PageView::query()->distinct()->count('visitor_hash');
    }

    public function countryCount(): int
    {
        return PageView::query()->whereNotNull('country_code')->distinct()->count('country_code');
    }

    /**
     * @return Collection<int, PageView>
     */
    public function countryBreakdown(?CarbonInterface $since = null, int $limit = 100): Collection
    {
        return PageView::query()
            ->selectRaw('MIN(id) as id, country_code, country_name, COUNT(DISTINCT visitor_hash) as unique_visitors, COUNT(*) as page_views')
            ->when($since, fn ($query) => $query->where('viewed_at', '>=', $since))
            ->groupBy('country_code', 'country_name')
            ->orderByDesc('unique_visitors')
            ->limit($limit)
            ->get();
    }

    /** @return array<int, array{date: string, label: string, count: int}> */
    public function dailyPageViews(int $days = 7): array
    {
        $days = max(1, $days);
        $start = now()->startOfDay()->subDays($days - 1);
        $counts = PageView::query()
            ->selectRaw('DATE(viewed_at) as view_date, COUNT(*) as page_views')
            ->where('viewed_at', '>=', $start)
            ->groupBy('view_date')
            ->pluck('page_views', 'view_date');

        return collect(range(0, $days - 1))
            ->map(function (int $offset) use ($start, $counts): array {
                $date = $start->copy()->addDays($offset);
                $key = $date->toDateString();

                return [
                    'date' => $key,
                    'label' => $date->format('d/m'),
                    'count' => (int) $counts->get($key, 0),
                ];
            })
            ->all();
    }

    /** @return array{visitors: int, countries: int, page_views: int, daily_page_views: array<int, array{date: string, label: string, count: int}>, top_countries: Collection<int, PageView>, all_countries: Collection<int, PageView>} */
    public function publicSummary(): array
    {
        $allCountries = $this->countryBreakdown(limit: 250);

        return [
            'visitors' => $this->uniqueVisitors(),
            'countries' => $this->countryCount(),
            'page_views' => PageView::query()->count(),
            'daily_page_views' => $this->dailyPageViews(),
            'top_countries' => $allCountries->take(8),
            'all_countries' => $allCountries,
        ];
    }
}
