<x-layouts.public :metadata="$metadata">
    <article class="mx-auto max-w-3xl">
        <a href="{{ route('articles.index') }}" class="text-sm font-semibold text-orange-700">&larr; Kembali ke artikel</a>
        @if ($article->featuredMedia)<img src="{{ $article->featuredMedia->url }}" alt="{{ $article->featuredMedia->alt_text ?: $article->title }}" class="my-7 aspect-[16/9] w-full rounded-2xl object-cover">@endif
        @if ($article->category)<p class="mt-7 text-sm font-bold uppercase tracking-wide text-orange-700">{{ $article->category->name }}</p>@endif
        <h1 class="mt-3 text-4xl font-black tracking-tight sm:text-5xl">{{ $article->title }}</h1>
        <p class="mt-4 text-sm text-stone-500">{{ $article->published_at->translatedFormat('d F Y') }}@if ($article->author) · {{ $article->author->name }}@endif</p>
        @if ($article->excerpt)<p class="mt-6 text-xl leading-8 text-stone-600">{{ $article->excerpt }}</p>@endif
        <div class="article-content mt-9 leading-8 text-stone-700">{!! $content !!}</div>
    </article>
</x-layouts.public>
