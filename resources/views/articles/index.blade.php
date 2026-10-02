<x-layouts.public :metadata="$metadata">
    <section class="page-intro"><p class="eyebrow">Cerita D’Mentai</p><h1>Artikel</h1><p>Artikel yang diterbitkan oleh D’Mentai.</p></section>
    @if ($categories->isNotEmpty())
        <nav aria-label="Kategori artikel" class="mb-8 flex flex-wrap gap-2">
            <a href="{{ route('articles.index') }}" class="rounded-full px-4 py-2 text-sm font-semibold {{ $selectedCategory ? 'bg-white text-stone-700 ring-1 ring-stone-200' : 'bg-orange-600 text-white' }}">Semua</a>
            @foreach ($categories as $category)
                <a href="{{ route('articles.index', ['kategori' => $category->slug]) }}" class="rounded-full px-4 py-2 text-sm font-semibold {{ $selectedCategory?->is($category) ? 'bg-orange-600 text-white' : 'bg-white text-stone-700 ring-1 ring-stone-200' }}">{{ $category->name }}</a>
            @endforeach
        </nav>
    @endif
    @if ($articles->isEmpty())
        <p class="empty-state">Belum ada artikel yang diterbitkan.</p>
    @else
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($articles as $article)
                <a href="{{ route('articles.show', $article->slug) }}" class="content-card">
                    @if ($article->featuredMedia)<img src="{{ $article->featuredMedia->url }}" alt="{{ $article->featuredMedia->alt_text ?: $article->title }}" class="aspect-[16/10] w-full object-cover" loading="lazy">@endif
                    <div class="card-body">
                        @if ($article->category)<p class="card-kicker">{{ $article->category->name }}</p>@endif
                        <h2>{{ $article->title }}</h2>
                        @if ($article->excerpt)<p>{{ $article->excerpt }}</p>@endif
                        <p class="small-note">{{ $article->published_at->locale('id')->translatedFormat('d F Y') }}</p>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-10">{{ $articles->links() }}</div>
    @endif
</x-layouts.public>
