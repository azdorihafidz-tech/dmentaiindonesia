<?php

namespace App\Services;

use App\Data\SeoMetadata;
use App\Models\Page;
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
        $image = $this->settings->media('seo.default_og_media_id');

        return new SeoMetadata(
            title: $title,
            description: $description,
            canonical: route('home'),
            robots: 'index,follow',
            ogTitle: $title,
            ogDescription: $description,
            ogImage: $image?->url,
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
            : ($page->excerpt ?: ($contentDescription ?: $this->settings->get('seo.meta_description', 'Informasi dari '.$siteTitle.'.')));
        $image = $page->ogMedia ?: $this->settings->media('seo.default_og_media_id');

        return new SeoMetadata(
            title: $title,
            description: $description,
            canonical: $page->canonical_url ?: route('pages.show', $page->slug),
            robots: $page->robots_directive ?: 'index,follow',
            ogTitle: $page->og_title ?: $title,
            ogDescription: $page->og_description ?: $description,
            ogImage: $image?->url,
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
            : ($content->getAttribute($summaryField) ?: ($bodyDescription ?: $this->settings->get('seo.meta_description', 'Informasi '.$name.' dari '.$siteTitle.'.')));
        $image = $content->ogMedia ?: $this->settings->media('seo.default_og_media_id');
        $slug = $content->getAttribute('slug');

        return new SeoMetadata(
            title: $title,
            description: $description,
            canonical: $content->canonical_url ?: route($routeName, $slug),
            robots: $content->robots_directive ?: 'index,follow',
            ogTitle: $content->og_title ?: $title,
            ogDescription: $content->og_description ?: $description,
            ogImage: $image?->url,
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
}
