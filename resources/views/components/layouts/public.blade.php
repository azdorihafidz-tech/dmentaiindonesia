<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f97316">
    <x-seo :metadata="$metadata" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-50 text-stone-950 antialiased">
    <header class="border-b border-stone-200 bg-white">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-5 py-4 sm:px-6">
            <a href="{{ route('home') }}" class="font-black text-orange-700">{{ app(\App\Services\SiteSettings::class)->get('brand.site_name', config('app.name')) }}</a>
            <nav aria-label="Navigasi utama" class="flex flex-wrap gap-3 text-sm font-semibold sm:gap-6">
                <a href="{{ route('products.index') }}" class="text-stone-700 hover:text-orange-700">Menu</a>
                @if (\Illuminate\Support\Facades\Route::has('promotions.index'))<a href="{{ route('promotions.index') }}" class="text-stone-700 hover:text-orange-700">Promo</a>@endif
                @if (\Illuminate\Support\Facades\Route::has('outlets.index'))<a href="{{ route('outlets.index') }}" class="text-stone-700 hover:text-orange-700">Outlet</a>@endif
                @if (\Illuminate\Support\Facades\Route::has('articles.index'))<a href="{{ route('articles.index') }}" class="text-stone-700 hover:text-orange-700">Artikel</a>@endif
                @if (\Illuminate\Support\Facades\Route::has('gallery.index'))<a href="{{ route('gallery.index') }}" class="text-stone-700 hover:text-orange-700">Galeri</a>@endif
                @if (\Illuminate\Support\Facades\Route::has('faqs.index'))<a href="{{ route('faqs.index') }}" class="text-stone-700 hover:text-orange-700">FAQ</a>@endif
            </nav>
        </div>
    </header>
    <main class="mx-auto max-w-6xl px-5 py-10 sm:px-6 sm:py-14">{{ $slot }}</main>
    <footer class="border-t border-stone-200 bg-white px-5 py-8 text-center text-sm text-stone-600">
        {{ app(\App\Services\SiteSettings::class)->get('footer.copyright', '© '.date('Y').' '.app(\App\Services\SiteSettings::class)->get('brand.site_name', config('app.name'))) }}
    </footer>
</body>
</html>
