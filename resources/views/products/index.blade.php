<x-layouts.public :metadata="$metadata">
    <div class="mb-10 max-w-2xl">
        <p class="text-sm font-bold uppercase tracking-[0.2em] text-orange-700">D'Mentai</p>
        <h1 class="mt-2 text-4xl font-black tracking-tight sm:text-5xl">Menu</h1>
        <p class="mt-4 text-stone-600">Pilihan menu yang tersedia dari D'Mentai.</p>
    </div>
    @if ($products->isEmpty())
        <p class="rounded-2xl border border-dashed border-stone-300 bg-white p-8 text-stone-600">Belum ada menu yang tersedia.</p>
    @else
        @foreach ($products->getCollection()->groupBy('category_id') as $categoryProducts)
            @php($category = $categoryProducts->first()->category)
            <section class="mb-12" aria-labelledby="category-{{ $category->id }}">
                <h2 id="category-{{ $category->id }}" class="mb-5 text-2xl font-bold">{{ $category->name }}</h2>
                @if ($category->description)<p class="mb-5 max-w-2xl text-stone-600">{{ $category->description }}</p>@endif
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($categoryProducts as $product)
                        <a href="{{ route('products.show', $product->slug) }}" class="overflow-hidden rounded-2xl border border-stone-200 bg-white transition hover:-translate-y-0.5 hover:shadow-md">
                            @if ($product->image)<img src="{{ $product->image->url }}" alt="{{ $product->image->alt_text ?: $product->name }}" class="aspect-[4/3] w-full object-cover" loading="lazy">@endif
                            <div class="p-5">
                                <h3 class="text-xl font-bold">{{ $product->name }}</h3>
                                @if ($product->short_description)<p class="mt-2 line-clamp-3 text-sm leading-6 text-stone-600">{{ $product->short_description }}</p>@endif
                                @if ($product->price !== null)<p class="mt-4 font-bold text-orange-700">Rp {{ number_format((float) ($product->promotional_price ?? $product->price), 0, ',', '.') }}</p>@endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
            </section>
        @endforeach
        <div class="mt-10">{{ $products->links() }}</div>
    @endif
</x-layouts.public>
