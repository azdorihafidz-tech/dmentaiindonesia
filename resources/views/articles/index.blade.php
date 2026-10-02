<x-layouts.public :metadata="$metadata">
    <div class="mb-8 max-w-2xl">
        <p class="text-sm font-bold uppercase tracking-[0.2em] text-orange-700">Cerita D'Mentai</p>
        <h1 class="mt-2 text-4xl font-black tracking-tight sm:text-5xl">Artikel</h1>
    </div>
    @if ($categories->isNotEmpty())
        <nav aria-label="Kategori artikel" class="mb-8 flex flex-wrap gap-2">
            <a href="{{ route('articles.index') }}" class="rounded-full px-4 py-2 text-sm font-semibold {{ $selectedCategory ? 'bg-white text-stone-700 ring-1 ring-stone-200' : 'bg-orange-600 text-white' }}">Semua</a>
            @foreach ($categories as $category)
                <a href="{{ route('articles.index', ['kategori' => $category->slug]) }}" class="rounded-full px-4 py-2 text-sm font-semibold {{ $selectedCategory?->is($category) ? 'bg-orange-600 text-white' : 'bg-white text-stone-700 ring-1 ring-stone-200' }}">{{ $category->name }}</a>
            @endforeach
        </nav>
    @endif
    @if ($articles->isEmpty())
        <p class="rounded-2xl border border-dashed border-stone-300 bg-white p-8 text-stone-600">Belum ada artikel yang diterbitkan.</p>
    @else
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($articles as $article)
                <a href="{{ route('articles.show', $article->slug) }}" class="overflow-hidden rounded-2xl border border-stone-200 bg-white hover:shadow-md">
                    @if ($article->featuredMedia)<img src="{{ $article->featuredMedia->url }}" alt="{{ $article->featuredMedia->alt_text ?: $article->title }}" class="aspect-[16/10] w-full object-cover" loading="lazy">@endif
                    <div class="p-5">
                        @if ($article->category)<p class="text-xs font-bold uppercase tracking-wide text-orange-700">{{ $article->category->name }}</p>@endif
                        <h2 class="mt-2 text-xl font-bold">{{ $article->title }}</h2>
                        @if ($article->excerpt)<p class="mt-2 line-clamp-3 text-sm leading-6 text-stone-600">{{ $article->excerpt }}</p>@endif
                        <p class="mt-4 text-xs text-stone-500">{{ $article->published_at->translatedFormat('d F Y') }}</p>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-10">{{ $articles->links() }}</div>
    @endif
</x-layouts.public>
