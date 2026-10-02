<x-layouts.public :metadata="$metadata">
    <section class="page-intro"><p class="eyebrow">Kenali pilihan kami</p><h1>Menu</h1><p>Jelajahi pilihan menu D’Mentai.</p></section>
    @if($categories->isNotEmpty())<nav class="filter-list" aria-label="Filter kategori menu"><a class="filter-chip {{ !$selectedCategory ? 'is-selected' : '' }}" href="{{ route('products.index') }}">Semua menu</a>@foreach($categories as $category)<a class="filter-chip {{ $selectedCategory?->is($category) ? 'is-selected' : '' }}" href="{{ route('products.index', ['kategori' => $category->slug]) }}" @if($selectedCategory?->is($category)) aria-current="page" @endif>{{ $category->name }}</a>@endforeach</nav>@endif
    @if($products->isEmpty())<p class="empty-state">{{ $selectedCategory ? 'Belum ada menu dalam kategori ini.' : 'Belum ada menu yang tersedia.' }}</p>@else
        <div class="card-grid product-grid">@foreach($products as $product)<a class="content-card product-card" href="{{ route('products.show', $product->slug) }}">
            @if($product->image)<img class="card-image" src="{{ $product->image->url }}" alt="{{ $product->image->alt_text ?: $product->name }}" loading="lazy">@else<div class="image-placeholder" aria-hidden="true">{{ mb_substr($product->name, 0, 1) }}</div>@endif
            <div class="card-body"><p class="card-kicker">{{ $product->category?->name }}</p><h2>{{ $product->name }}</h2>@if($product->short_description)<p>{{ $product->short_description }}</p>@endif@if($product->price !== null)<strong class="price">Rp {{ number_format((float)($product->promotional_price ?? $product->price), 0, ',', '.') }}</strong>@endif</div>
        </a>@endforeach</div><div class="pagination-wrap">{{ $products->links() }}</div>
    @endif
</x-layouts.public>
