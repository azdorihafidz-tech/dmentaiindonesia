<?php

namespace App\Services;

use App\Data\SeoMetadata;
use App\Models\Article;
use App\Models\Outlet;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SeoManager
{
    public function __construct(
        private readonly SiteSettings $settings,
        private readonly PageContentSanitizer $sanitizer,
    ) {}

    public function homepage(): SeoMetadata
    {
        $title = $this->settings->get('seo.default_title')
            ?: $this->settings->get('brand.site_name', config('app.name'));
        $description = $this->settings->get('seo.meta_description')
            ?: 'Situs resmi '.$this->settings->get('brand.site_name', config('app.name')).'.';
        $ogTitle = $this->settings->get('seo.og_title') ?: $title;
        $ogDescription = $this->settings->get('seo.og_description') ?: $description;
        $image = $this->settings->media('seo.default_og_media_id');
        $canonical = route('home');

        return new SeoMetadata(
            title: $title,
            description: $description,
            canonical: $canonical,
            robots: 'index,follow',
            ogTitle: $ogTitle,
            ogDescription: $ogDescription,
            ogImage: $image?->url,
            structuredData: $this->websiteStructuredData($canonical),
        );
    }

    public function forPage(Page $page): SeoMetadata
    {
        $siteTitle = $this->settings->get('seo.default_title')
            ?: $this->settings->get('brand.site_name', config('app.name'));
        $title = filled($page->seo_title) ? $page->seo_title : ($page->title ?: $siteTitle);
        $content = $this->sanitizer->sanitize($page->content);
        $plainContent = trim(preg_replace('/\\s+/u', ' ', html_entity_decode(strip_tags($content), ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '');
        $contentDescription = $plainContent !== '' ? Str::limit($plainContent, 160, '') : null;
        $description = filled($page->seo_description)
            ? $page->seo_description
            : ($page->excerpt ?: ($contentDescription ?: ($this->settings->get('seo.meta_description') ?: 'Informasi dari '.$siteTitle.'.')));
        $image = $page->ogMedia ?: $page->featuredMedia ?: $this->settings->media('seo.default_og_media_id');
        $canonical = $page->canonical_url ?: route('pages.show', $page->slug);

        return new SeoMetadata(
            title: $title,
            description: $description,
            canonical: $canonical,
            robots: $page->robots_directive ?: 'index,follow',
            ogTitle: $page->og_title ?: $title,
            ogDescription: $page->og_description ?: $description,
            ogImage: $image?->url,
            structuredData: $this->contentStructuredData($page, $canonical, $page->title, 'pages.show'),
        );
    }

    public function forContent(Model $content, string $routeName, string $nameField, string $summaryField, ?string $bodyField = null): SeoMetadata
    {
        $siteTitle = $this->settings->get('seo.default_title')
            ?: $this->settings->get('brand.site_name', config('app.name'));
        $name = (string) $content->getAttribute($nameField);
        $title = filled($content->seo_title) ? $content->seo_title : (filled($name) ? "{$name} | {$siteTitle}" : $siteTitle);
        $body = $bodyField ? $this->sanitizer->sanitize($content->getAttribute($bodyField)) : '';
        $plainBody = trim(preg_replace('/\\s+/u', ' ', html_entity_decode(strip_tags($body), ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '');
        $bodyDescription = $plainBody !== '' ? Str::limit($plainBody, 160, '') : null;
        $description = filled($content->seo_description)
            ? $content->seo_description
            : ($content->getAttribute($summaryField) ?: ($bodyDescription ?: ($this->settings->get('seo.meta_description') ?: 'Informasi '.$name.' dari '.$siteTitle.'.')));
        $featuredImage = match (true) {
            $content instanceof Article => $content->featuredMedia,
            $content instanceof Product, $content instanceof Outlet => $content->image,
            default => $content->getRelationValue('banner'),
        };
        $image = $content->ogMedia ?: $featuredImage ?: $this->settings->media('seo.default_og_media_id');
        $slug = $content->getAttribute('slug');
        $canonical = $content->canonical_url ?: route($routeName, $slug);

        return new SeoMetadata(
            title: $title,
            description: $description,
            canonical: $canonical,
            robots: $content->robots_directive ?: 'index,follow',
            ogTitle: $content->og_title ?: $title,
            ogDescription: $content->og_description ?: $description,
            ogImage: $image?->url,
            structuredData: $this->contentStructuredData($content, $canonical, $name, $routeName),
        );
    }

    public function forListing(string $title, string $routeName, ?string $description = null): SeoMetadata
    {
        $siteTitle = $this->settings->get('seo.default_title')
            ?: $this->settings->get('brand.site_name', config('app.name'));
        $fullTitle = "{$title} | {$siteTitle}";
        $fallbackDescription = $this->settings->get('seo.meta_description') ?: "Informasi {$title} dari {$siteTitle}.";
        $image = $this->settings->media('seo.default_og_media_id');

        return new SeoMetadata(
            title: $fullTitle,
            description: $description ?: $fallbackDescription,
            canonical: route($routeName),
            robots: 'index,follow',
            ogTitle: $fullTitle,
            ogDescription: $description ?: $fallbackDescription,
            ogImage: $image?->url,
        );
    }

    /** @return array<string, mixed> */
    private function websiteStructuredData(string $canonical): array
    {
        $name = $this->settings->get('brand.site_name') ?: config('app.name');
        $organizationId = $canonical.'#organization';
        $websiteId = $canonical.'#website';
        $logo = $this->settings->media('brand.logo_media_id');
        $sameAs = array_values(array_filter([
            $this->settings->get('social.instagram'),
            $this->settings->get('social.facebook'),
            $this->settings->get('social.tiktok'),
            $this->settings->get('social.youtube'),
        ], fn (?string $url): bool => filled($url)));

        $organization = [
            '@type' => 'Organization',
            '@id' => $organizationId,
            'name' => $name,
            'url' => $canonical,
        ];

        if ($description = $this->settings->get('brand.description')) {
            $organization['description'] = $description;
        }

        if ($logo) {
            $organization['logo'] = ['@type' => 'ImageObject', 'url' => $logo->url];
        }

        if ($sameAs !== []) {
            $organization['sameAs'] = $sameAs;
        }

        return [
            '@context' => 'https://schema.org',
            '@graph' => [
                $organization,
                [
                    '@type' => 'WebSite',
                    '@id' => $websiteId,
                    'url' => $canonical,
                    'name' => $name,
                    'inLanguage' => 'id-ID',
                    'publisher' => ['@id' => $organizationId],
                ],
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function contentStructuredData(Model $content, string $canonical, string $name, ?string $routeName): array
    {
        $nodes = [];
        $breadcrumbs = $this->breadcrumbs($canonical, $name, $routeName);

        if ($breadcrumbs !== []) {
            $nodes[] = ['@type' => 'BreadcrumbList', 'itemListElement' => $breadcrumbs];
        }

        if ($content instanceof Article) {
            $article = [
                '@type' => 'Article',
                'headline' => $content->title,
                'datePublished' => $content->published_at?->toIso8601String(),
                'dateModified' => $content->updated_at?->toIso8601String(),
                'inLanguage' => 'id-ID',
                'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $canonical],
                'publisher' => ['@type' => 'Organization', 'name' => $this->settings->get('brand.site_name', config('app.name'))],
            ];

            if ($content->excerpt) {
                $article['description'] = $content->excerpt;
            }

            if ($content->category) {
                $article['articleSection'] = $content->category->name;
            }

            if ($content->featuredMedia) {
                $article['image'] = [$content->featuredMedia->url];
            }

            if ($content->author) {
                $article['author'] = ['@type' => 'Person', 'name' => $content->author->name];
            }

            $nodes[] = $article;
        }

        if ($content instanceof Product && $content->price !== null) {
            $product = [
                '@type' => 'Product',
                'name' => $content->name,
                'offers' => [
                    '@type' => 'Offer',
                    'url' => $canonical,
                    'priceCurrency' => 'IDR',
                    'price' => (string) ($content->promotional_price ?? $content->price),
                ],
            ];

            if ($content->short_description) {
                $product['description'] = $content->short_description;
            }

            if ($content->image) {
                $product['image'] = [$content->image->url];
            }

            $nodes[] = $product;
        }

        if ($content instanceof Outlet) {
            $address = array_filter([
                '@type' => 'PostalAddress',
                'streetAddress' => $content->address,
                'addressLocality' => $content->city,
                'addressRegion' => $content->province,
                'addressCountry' => $content->country,
            ], fn ($value): bool => filled($value));

            if (filled($content->address)) {
                $business = [
                    '@type' => 'LocalBusiness',
                    'name' => $content->name,
                    'url' => $canonical,
                    'address' => $address,
                ];

                if ($content->phone) {
                    $business['telephone'] = $content->phone;
                }

                if ($content->google_maps_url) {
                    $business['hasMap'] = $content->google_maps_url;
                }

                if ($content->latitude !== null && $content->longitude !== null) {
                    $business['geo'] = [
                        '@type' => 'GeoCoordinates',
                        'latitude' => (float) $content->latitude,
                        'longitude' => (float) $content->longitude,
                    ];
                }

                $nodes[] = $business;
            }
        }

        return $nodes === [] ? [] : ['@context' => 'https://schema.org', '@graph' => $nodes];
    }

    /** @return list<array<string, mixed>> */
    private function breadcrumbs(string $canonical, string $name, ?string $routeName): array
    {
        $parent = match ($routeName) {
            'products.show' => ['Menu', route('products.index')],
            'promotions.show' => ['Promo', route('promotions.index')],
            'outlets.show' => ['Outlet', route('outlets.index')],
            'articles.show' => ['Artikel', route('articles.index')],
            'pages.show' => null,
            default => null,
        };

        $items = [['Beranda', route('home')]];

        if ($parent) {
            $items[] = $parent;
        }

        if ($routeName) {
            $items[] = [$name, $canonical];
        }

        return count($items) < 2 ? [] : array_map(
            fn (array $item, int $index): array => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item[0],
                'item' => $item[1],
            ],
            $items,
            array_keys($items),
        );
    }
}
