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
    <header class="border-b border-stone-200 bg-white"><div class="mx-auto flex max-w-5xl items-center justify-between px-6 py-5">
        <a href="{{ route('home') }}" class="font-black text-orange-600">{{ $settings->get('brand.site_name', config('app.name')) }}</a>
        <a href="{{ route('home') }}" class="text-sm font-semibold text-stone-600">Beranda</a>
    </div></header>
    <main class="mx-auto max-w-3xl px-6 py-12 sm:py-20">
        @if ($page->featuredMedia)
            <img src="{{ $page->featuredMedia->url }}" alt="{{ $page->featuredMedia->alt_text ?: $page->title }}" class="mb-8 aspect-[16/9] w-full rounded-2xl object-cover" loading="lazy">
        @endif
        <article>
            <h1 class="text-4xl font-black tracking-tight sm:text-5xl">{{ $page->title }}</h1>
            @if ($page->excerpt)<p class="mt-5 text-lg leading-8 text-stone-600">{{ $page->excerpt }}</p>@endif
            <div class="article-content mt-10 leading-8 text-stone-700">{!! $content !!}</div>
        </article>
    </main>
    <footer class="border-t border-stone-200 px-6 py-8 text-center text-sm text-stone-500">{{ $settings->get('footer.copyright', '© '.date('Y').' '.$settings->get('brand.site_name', config('app.name'))) }}</footer>
</body>
</html>
