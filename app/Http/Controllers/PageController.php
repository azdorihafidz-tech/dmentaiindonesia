<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Services\PageContentSanitizer;
use App\Services\SeoManager;
use App\Services\SiteSettings;
use Illuminate\View\View;

class PageController extends Controller
{
    public function show(string $slug, PageContentSanitizer $sanitizer, SeoManager $seo, SiteSettings $settings): View
    {
        $page = Page::published()->with(['featuredMedia', 'ogMedia'])->where('slug', $slug)->firstOrFail();

        return view('pages.show', [
            'page' => $page,
            'content' => $sanitizer->sanitize($page->content),
            'metadata' => $seo->forPage($page),
            'settings' => $settings,
        ]);
    }
}
