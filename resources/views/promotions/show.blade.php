<x-layouts.public :metadata="$metadata">
    <nav aria-label="Breadcrumb" class="mb-6 text-sm text-stone-500"><a class="text-link" href="{{ route('home') }}">Beranda</a> <span aria-hidden="true">/</span> <a class="text-link" href="{{ route('promotions.index') }}">Promosi</a> <span aria-hidden="true">/</span> {{ $promotion->title }}</nav>
    <article class="mx-auto mt-6 max-w-3xl">
        @if ($promotion->banner)<img src="{{ $promotion->banner->url }}" alt="{{ $promotion->banner->alt_text ?: $promotion->title }}" class="mb-8 aspect-[16/9] w-full rounded-2xl object-cover">@endif
        <h1 class="text-4xl font-black tracking-tight sm:text-5xl">{{ $promotion->title }}</h1>
        @if ($promotion->short_description)<p class="mt-5 text-lg leading-8 text-stone-600">{{ $promotion->short_description }}</p>@endif
        @if ($promotion->start_at || $promotion->end_at)<p class="mt-4 text-sm text-stone-500">@if ($promotion->start_at)Mulai {{ $promotion->start_at->locale('id')->translatedFormat('d F Y') }}@endif @if ($promotion->end_at){{ $promotion->start_at ? '— ' : '' }}sampai {{ $promotion->end_at->locale('id')->translatedFormat('d F Y') }}@endif</p>@endif
        <div class="article-content mt-8 leading-8 text-stone-700">{!! app(\App\Services\PageContentSanitizer::class)->sanitize($promotion->description) !!}</div>
        @if ($promotion->cta_text && $promotion->cta_url)<a href="{{ $promotion->cta_url }}" class="mt-8 inline-flex rounded-full bg-orange-600 px-6 py-3 font-bold text-white hover:bg-orange-700" rel="nofollow">{{ $promotion->cta_text }}</a>@endif
    </article>
</x-layouts.public>
