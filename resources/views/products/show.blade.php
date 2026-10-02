<x-layouts.public :metadata="$metadata">
    <a href="{{ route('products.index') }}" class="text-sm font-semibold text-orange-700">&larr; Kembali ke menu</a>
    <article class="mt-6 grid gap-8 lg:grid-cols-2 lg:items-start">
        @if ($product->image)<img src="{{ $product->image->url }}" alt="{{ $product->image->alt_text ?: $product->name }}" class="w-full rounded-2xl object-cover" loading="eager">@endif
        <div>
            <p class="text-sm font-semibold text-orange-700">{{ $product->category->name }}</p>
            <h1 class="mt-2 text-4xl font-black tracking-tight">{{ $product->name }}</h1>
            @if ($product->short_description)<p class="mt-4 text-lg leading-8 text-stone-600">{{ $product->short_description }}</p>@endif
            @if ($product->price !== null)
                <p class="mt-6 text-xl font-extrabold text-orange-700">Rp {{ number_format((float) ($product->promotional_price ?? $product->price), 0, ',', '.') }}</p>
                @if ($product->promotional_price !== null)<p class="mt-1 text-sm text-stone-500 line-through">Rp {{ number_format((float) $product->price, 0, ',', '.') }}</p>@endif
            @endif
            <div class="article-content mt-8 leading-8 text-stone-700">{!! app(\App\Services\PageContentSanitizer::class)->sanitize($product->description) !!}</div>
        </div>
    </article>
</x-layouts.public>
