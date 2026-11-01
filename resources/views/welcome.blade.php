<x-layouts.public :metadata="$metadata">
    @php
        $mascot = $settings->media('brand.mascot_media_id');
        $showMascot = $settings->get('brand.mascot_enabled', '1') !== '0';
        $mascotImage = $mascot?->url ?? asset('images/brand/dmentai-mascot.webp');
        $mascotAlt = $settings->get('brand.mascot_alt_text') ?: ($mascot?->alt_text ?: "Maskot D'Mentai sedang menikmati dimsum");
        $whatsappUrl = app(\App\Services\WhatsAppLink::class)->url($settings->get('contact.whatsapp'));
    @endphp
    <section @class(['hero-section', 'hero-section-with-mascot' => $showMascot])>
        <div class="hero-copy">
            <p class="eyebrow">{{ $settings->get('brand.tagline', 'D’Mentai Indonesia') }}</p>
            <h1>{{ $settings->get('brand.site_name', "D'Mentai Indonesia") }}</h1>
            @if($settings->get('brand.description'))<p class="hero-description">{{ $settings->get('brand.description') }}</p>@endif
            <div class="hero-actions"><a class="button button-primary" href="{{ route('products.index') }}">Lihat menu <span aria-hidden="true">↗</span></a><a class="button button-light" href="{{ route('outlets.index') }}">Cari outlet</a></div>
        </div>
        @if($showMascot)
            <div class="hero-mascot-art">
                <span class="hero-mascot-backdrop" aria-hidden="true"></span>
                <img src="{{ $mascotImage }}" alt="{{ $mascotAlt }}" width="1134" height="839" loading="eager" fetchpriority="high">
            </div>
        @endif
        <div class="hero-bottom"><span>Menu</span><span>Promosi</span><span>Outlet</span><span>Indonesia</span></div>
    </section>

    @if($products->isNotEmpty())<section class="content-section section-shell" aria-labelledby="featured-heading">
        <div class="section-heading"><div><p class="eyebrow">Dari menu kami</p><h2 id="featured-heading">Pilihan favorit</h2></div><a class="text-link" href="{{ route('products.index') }}">Lihat semua menu <span aria-hidden="true">→</span></a></div>
        <div class="card-grid product-grid">@foreach($products as $product)<a class="content-card product-card" href="{{ route('products.show', $product->slug) }}">
            @if($product->image)<img class="card-image" src="{{ $product->image->url }}" alt="{{ $product->image->alt_text ?: $product->name }}" loading="lazy">@else<div class="image-placeholder" aria-hidden="true">{{ mb_substr($product->name, 0, 1) }}</div>@endif
            <div class="card-body"><p class="card-kicker">{{ $product->category?->name }}</p><h3>{{ $product->name }}</h3>@if($product->short_description)<p>{{ $product->short_description }}</p>@endif @if($product->price !== null)<strong class="price">Rp {{ number_format((float)($product->promotional_price ?? $product->price), 0, ',', '.') }}</strong>@endif</div>
        </a>@endforeach</div>
    </section>@endif

    @if($categories->isNotEmpty())<section class="content-section category-band" aria-labelledby="categories-heading"><div class="section-shell"><div class="section-heading"><div><p class="eyebrow">Jelajahi</p><h2 id="categories-heading">Pilih sesuai selera</h2></div><a class="text-link" href="{{ route('products.index') }}">Semua menu →</a></div><div class="category-list">@foreach($categories as $category)<a class="category-chip" href="{{ route('products.index', ['kategori' => $category->slug]) }}">@if($category->image)<img src="{{ $category->image->url }}" alt="" loading="lazy">@endif<span>{{ $category->name }}</span><span aria-hidden="true">↗</span></a>@endforeach</div></div></section>@endif

    @if($promotion)<section class="content-section section-shell" aria-labelledby="promotion-heading"><div class="promotion-feature">
        @if($promotion->banner)<img src="{{ $promotion->banner->url }}" alt="{{ $promotion->banner->alt_text ?: $promotion->title }}" loading="lazy">@endif
        <div class="promotion-copy"><p class="eyebrow">Sedang berlangsung</p><h2 id="promotion-heading">{{ $promotion->title }}</h2>@if($promotion->short_description)<p>{{ $promotion->short_description }}</p>@endif<a class="button button-primary" href="{{ route('promotions.show', $promotion->slug) }}">Lihat promosi <span aria-hidden="true">→</span></a></div>
    </div></section>@endif

    @if($outlets->isNotEmpty())<section class="content-section section-shell" aria-labelledby="outlets-heading"><div class="section-heading"><div><p class="eyebrow">Kunjungi kami</p><h2 id="outlets-heading">Temukan outlet</h2></div><a class="text-link" href="{{ route('outlets.index') }}">Semua outlet →</a></div><div class="card-grid outlet-grid">@foreach($outlets as $outlet)<a class="content-card outlet-card" href="{{ route('outlets.show', $outlet->slug) }}">@if($outlet->image)<img class="card-image" src="{{ $outlet->image->url }}" alt="{{ $outlet->image->alt_text ?: $outlet->name }}" loading="lazy">@endif<div class="card-body"><h3>{{ $outlet->name }}</h3>@if($outlet->address)<p>{{ $outlet->address }}</p>@endif@if($outlet->business_hours)<p class="small-note">{{ $outlet->business_hours }}</p>@endif</div></a>@endforeach</div></section>@endif

    @if($testimonials->isNotEmpty())<section class="content-section testimonial-band" aria-labelledby="testimonials-heading"><div class="section-shell"><div class="section-heading"><div><p class="eyebrow">Cerita pelanggan</p><h2 id="testimonials-heading">Kesan yang dibagikan</h2></div></div><div class="card-grid">@foreach($testimonials as $testimonial)<figure class="quote-card"><blockquote>“{{ $testimonial->content }}”</blockquote><figcaption>@if($testimonial->image)<img src="{{ $testimonial->image->url }}" alt="" loading="lazy">@endif{{ $testimonial->customer_name }}</figcaption></figure>@endforeach</div></div></section>@endif

    @if($gallery->isNotEmpty())<section class="content-section section-shell" aria-labelledby="gallery-heading"><div class="section-heading"><div><p class="eyebrow">Momen D’Mentai</p><h2 id="gallery-heading">Galeri</h2></div><a class="text-link" href="{{ route('gallery.index') }}">Lihat galeri →</a></div><div class="home-gallery">@foreach($gallery as $item)<a href="{{ route('gallery.index') }}" aria-label="{{ $item->alt_text ?: $item->title ?: 'Lihat galeri' }}"><img src="{{ $item->image->url }}" alt="{{ $item->alt_text ?: $item->image->alt_text ?: $item->title }}" loading="lazy"></a>@endforeach</div></section>@endif

    @if($articles->isNotEmpty())<section class="content-section section-shell" aria-labelledby="articles-heading"><div class="section-heading"><div><p class="eyebrow">Cerita & kabar</p><h2 id="articles-heading">Dari D’Mentai</h2></div><a class="text-link" href="{{ route('articles.index') }}">Semua artikel →</a></div><div class="card-grid">@foreach($articles as $article)<a class="content-card article-card" href="{{ route('articles.show', $article->slug) }}">@if($article->featuredMedia)<img class="card-image" src="{{ $article->featuredMedia->url }}" alt="{{ $article->featuredMedia->alt_text ?: $article->title }}" loading="lazy">@endif<div class="card-body">@if($article->category)<p class="card-kicker">{{ $article->category->name }}</p>@endif<h3>{{ $article->title }}</h3>@if($article->excerpt)<p>{{ $article->excerpt }}</p>@endif<p class="small-note">{{ $article->published_at->locale('id')->translatedFormat('d F Y') }}</p></div></a>@endforeach</div></section>@endif

    @if($faqs->isNotEmpty())<section class="content-section faq-band" aria-labelledby="home-faq-heading"><div class="section-shell faq-layout"><div><p class="eyebrow">Ada pertanyaan?</p><h2 id="home-faq-heading">Kami bantu jawab.</h2><a class="text-link" href="{{ route('faqs.index') }}">Lihat semua pertanyaan →</a></div><div class="faq-stack">@foreach($faqs as $faq)<details class="faq-item"><summary>{{ $faq->question }}<span aria-hidden="true">+</span></summary><p>{{ $faq->answer }}</p></details>@endforeach</div></div></section>@endif

    <section class="final-cta"><p class="eyebrow">{{ $settings->get('brand.site_name', "D'Mentai Indonesia") }}</p><h2>Siap melihat lebih dekat?</h2><div class="hero-actions"><a class="button button-dark" href="{{ route('products.index') }}">Jelajahi menu <span aria-hidden="true">↗</span></a>@if($whatsappUrl)<a class="button button-outline" href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer">Hubungi kami</a>@endif</div></section>
    @if($settings->get('analytics.public_visitor_stats_enabled', '1') !== '0')
        <section class="visitor-stats-section content-section section-shell" aria-labelledby="visitor-stats-heading">
            <div class="visitor-stats-card">
                @php
                    $pageViewMax = max(1, collect($visitorStats['daily_page_views'])->max('count'));
                    $pageViewPoints = collect($visitorStats['daily_page_views'])->values()->map(function ($day, $index) use ($pageViewMax, $visitorStats) {
                        $x = $index * (100 / max(1, count($visitorStats['daily_page_views']) - 1));
                        $y = 34 - ((int) $day['count'] / $pageViewMax * 26);
                        return number_format($x, 2, '.', '').','.number_format($y, 2, '.', '');
                    });
                    $pageViewDigits = str_split((string) $visitorStats['page_views']);
                @endphp
                <div class="visitor-widget-top">
                    <div class="visitor-widget-title">
                        <p class="eyebrow">Statistik D'Mentai</p>
                        <h2 id="visitor-stats-heading">Page Views</h2>
                    </div>
                    <div class="visitor-pageview-counter">
                        <svg class="visitor-pageview-chart" viewBox="0 0 100 40" role="img" aria-label="Grafik page views tujuh hari terakhir">
                            <polygon points="{{ $pageViewPoints->implode(' ') }} 100,40 0,40" class="visitor-pageview-chart-fill"></polygon>
                            <polyline points="{{ $pageViewPoints->implode(' ') }}" class="visitor-pageview-chart-line"></polyline>
                        </svg>
                        <div class="visitor-counter-digits" role="img" aria-label="{{ number_format($visitorStats['page_views'], 0, ',', '.') }} page views">
                            @foreach($pageViewDigits as $digit)<span>{{ $digit }}</span>@endforeach
                        </div>
                    </div>
                </div>
                <div class="visitor-widget-visitors">
                    <div class="visitor-widget-visitors-heading">
                        <div><h3>Visitors</h3><p>Pengunjung menurut negara</p></div>
                        @if($visitorStats['all_countries']->count() > 8)
                            <details class="visitor-country-more">
                                <summary>Lihat semua</summary>
                                <div class="visitor-country-more-list">
                                    @foreach($visitorStats['all_countries'] as $country)
                                        <div class="visitor-country-row">
                                            <span><span class="visitor-country-flag" aria-hidden="true">{{ \App\Services\CountryPresentation::flag($country->country_code) }}</span>{{ $country->country_name ?: ($country->country_code ?: 'Belum diketahui') }}</span>
                                            <strong>{{ number_format((int) $country->unique_visitors, 0, ',', '.') }}</strong>
                                        </div>
                                    @endforeach
                                </div>
                            </details>
                        @endif
                    </div>
                    @if($visitorStats['top_countries']->isNotEmpty())
                        <div class="visitor-country-list" aria-label="Negara asal pengunjung terbanyak">
                            @foreach($visitorStats['top_countries'] as $country)
                                <div class="visitor-country-row">
                                    <span><span class="visitor-country-flag" aria-hidden="true">{{ \App\Services\CountryPresentation::flag($country->country_code) }}</span>{{ $country->country_name ?: ($country->country_code ?: 'Belum diketahui') }}</span>
                                    <strong>{{ number_format((int) $country->unique_visitors, 0, ',', '.') }}</strong>
                                </div>
                            @endforeach
                        </div>
                        @if($visitorStats['countries'] > 0)
                            <p class="visitor-geo-attribution">This product includes GeoLite Data created by <a href="https://www.maxmind.com" target="_blank" rel="noopener noreferrer">MaxMind</a>, available from maxmind.com.</p>
                        @elseif($visitorStats['page_views'] > 0)
                            <p class="visitor-country-empty">Negara belum terdeteksi. Data negara akan tampil setelah database GeoLite2 gratis dipasang.</p>
                        @endif
                    @else
                        <p class="visitor-country-empty">Belum ada data pengunjung yang tercatat.</p>
                    @endif
                </div>
            </div>
        </section>
    @endif
</x-layouts.public>
