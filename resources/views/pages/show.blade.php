<x-layouts.public :metadata="$metadata">
    <article class="mx-auto max-w-3xl py-8 sm:py-14">
        <a href="{{ route('home') }}" class="text-link">← Beranda</a>
        @if ($page->featuredMedia)<img src="{{ $page->featuredMedia->url }}" alt="{{ $page->featuredMedia->alt_text ?: $page->title }}" class="my-8 aspect-[16/9] w-full rounded-2xl object-cover" loading="eager">@endif
        <h1 class="mt-7 text-4xl font-black tracking-tight sm:text-5xl">{{ $page->title }}</h1>
        @if ($page->excerpt)<p class="mt-5 text-lg leading-8 text-stone-600">{{ $page->excerpt }}</p>@endif
        <div class="article-content mt-9 leading-8 text-stone-700">{!! $content !!}</div>
    </article>
</x-layouts.public>
