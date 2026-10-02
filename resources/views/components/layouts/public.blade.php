@php
    $site = app(\App\Services\SiteSettings::class);
    $siteName = $site->get('brand.site_name', config('app.name'));
    $logo = $site->media('brand.logo_media_id');
    $siteAddress = collect([$site->get('location.address'), $site->get('location.city'), $site->get('location.province'), $site->get('location.country')])->filter()->implode(', ');
    $links = [
        ['Menu', 'products.index'], ['Promo', 'promotions.index'], ['Outlet', 'outlets.index'],
        ['Galeri', 'gallery.index'], ['Artikel', 'articles.index'], ['FAQ', 'faqs.index'],
    ];
    $cmsPages = \App\Models\Page::published()->orderBy('title')->get(['title', 'slug']);
    $navigationPages = $cmsPages->whereIn('slug', ['tentang-kami', 'tentang-dmentai', 'kontak']);
    $contactPhone = $site->get('contact.whatsapp') ?: $site->get('contact.phone');
    $whatsapp = $site->get('contact.whatsapp');
    $socials = ['Instagram' => $site->get('social.instagram'), 'TikTok' => $site->get('social.tiktok'), 'Facebook' => $site->get('social.facebook'), 'YouTube' => $site->get('social.youtube')];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f36b21">
    @if ($favicon = $site->media('brand.favicon_media_id'))<link rel="icon" type="{{ $favicon->mime_type }}" href="{{ $favicon->url }}">@endif
    <x-seo :metadata="$metadata" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#fffaf5] text-stone-950 antialiased">
    <a class="skip-link" href="#main-content">Lewati ke konten</a>
    <header class="site-header">
        <div class="site-header-inner">
            <a href="{{ route('home') }}" class="brand-lockup" aria-label="{{ $siteName }} — Beranda">
                @if ($logo)<img src="{{ $logo->url }}" alt="{{ $logo->alt_text ?: $siteName }}" class="brand-logo">@endif
                <span class="brand-name">{{ $siteName }}</span>
            </a>
            <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="primary-navigation" aria-label="Buka navigasi"><span></span><span></span><span></span></button>
            <nav id="primary-navigation" class="primary-navigation" aria-label="Navigasi utama">
                <a href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>Beranda</a>
                @foreach ($links as [$label, $route])
                    @if (\Illuminate\Support\Facades\Route::has($route))<a href="{{ route($route) }}" @if(request()->routeIs($route, str_replace('.index', '.show', $route))) aria-current="page" @endif>{{ $label }}</a>@endif
                @endforeach
                @foreach ($navigationPages as $cmsPage)<a href="{{ route('pages.show', $cmsPage->slug) }}" @if(request()->is($cmsPage->slug)) aria-current="page" @endif>{{ $cmsPage->title }}</a>@endforeach
                @if ($whatsapp)<a class="nav-contact" href="https://wa.me/{{ preg_replace('/\D+/', '', $whatsapp) }}" target="_blank" rel="noopener noreferrer">Hubungi kami</a>@endif
            </nav>
        </div>
    </header>
    <main id="main-content" @class(['page-main' => ! request()->routeIs('home')])>{{ $slot }}</main>
    <footer class="site-footer">
        <div class="footer-grid">
            <div><a href="{{ route('home') }}" class="footer-brand">{{ $siteName }}</a>@if($site->get('brand.tagline'))<p class="footer-tagline">{{ $site->get('brand.tagline') }}</p>@endif @if($site->get('footer.text'))<p class="footer-copy">{{ $site->get('footer.text') }}</p>@endif</div>
            <div><h2 class="footer-heading">Jelajahi</h2><div class="footer-links"><a href="{{ route('products.index') }}">Menu</a><a href="{{ route('outlets.index') }}">Outlet</a><a href="{{ route('promotions.index') }}">Promo</a>@foreach($cmsPages as $cmsPage)<a href="{{ route('pages.show', $cmsPage->slug) }}">{{ $cmsPage->title }}</a>@endforeach</div></div>
            @if($contactPhone || $site->get('contact.email') || $siteAddress || $site->get('location.maps_url') || collect($socials)->filter()->isNotEmpty())
            <div><h2 class="footer-heading">Temukan kami</h2><div class="footer-links">
                @if($siteAddress)<p>{{ $siteAddress }}</p>@endif
                @if($site->get('location.maps_url'))<a href="{{ $site->get('location.maps_url') }}" target="_blank" rel="noopener noreferrer">Lihat peta</a>@endif
                @if($contactPhone)<a href="{{ $whatsapp ? 'https://wa.me/'.preg_replace('/\D+/', '', $whatsapp) : 'tel:'.preg_replace('/[^+\d]/', '', $contactPhone) }}" @if($whatsapp)target="_blank" rel="noopener noreferrer"@endif>{{ $whatsapp ? 'WhatsApp' : $contactPhone }}</a>@endif
                @if($site->get('contact.email'))<a href="mailto:{{ $site->get('contact.email') }}">{{ $site->get('contact.email') }}</a>@endif
                @foreach($socials as $name => $url)@if($url)<a href="{{ $url }}" target="_blank" rel="noopener noreferrer">{{ $name }}</a>@endif @endforeach
                @if($site->get('location.business_hours'))<p class="whitespace-pre-line">{{ $site->get('location.business_hours') }}</p>@endif
            </div></div>@endif
        </div>
        <div class="footer-bottom">{{ $site->get('footer.copyright', '© '.date('Y').' '.$siteName) }}</div>
    </footer>
</body>
</html>
