<?php

namespace App\Services;

use App\Data\SeoMetadata;
use App\Models\Page;
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
        $description = $this->settings->get('seo.meta_description');
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
            : ($page->excerpt ?: ($contentDescription ?: $this->settings->get('seo.meta_description')));
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
}
