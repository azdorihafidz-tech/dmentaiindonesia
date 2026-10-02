<x-layouts.public :metadata="$metadata">
    <div class="mb-10 max-w-2xl">
        <p class="text-sm font-bold uppercase tracking-[0.2em] text-orange-700">D'Mentai</p>
        <h1 class="mt-2 text-4xl font-black tracking-tight sm:text-5xl">Promosi</h1>
        <p class="mt-4 text-stone-600">Informasi promosi yang sedang berlaku.</p>
    </div>
    @if ($promotions->isEmpty())
        <p class="rounded-2xl border border-dashed border-stone-300 bg-white p-8 text-stone-600">Belum ada promosi yang tersedia.</p>
    @else
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($promotions as $promotion)
                <a href="{{ route('promotions.show', $promotion->slug) }}" class="overflow-hidden rounded-2xl border border-stone-200 bg-white hover:shadow-md">
                    @if ($promotion->banner)<img src="{{ $promotion->banner->url }}" alt="{{ $promotion->banner->alt_text ?: $promotion->title }}" class="aspect-[16/10] w-full object-cover" loading="lazy">@endif
                    <div class="p-5"><h2 class="text-xl font-bold">{{ $promotion->title }}</h2>
                        @if ($promotion->short_description)<p class="mt-2 line-clamp-3 text-sm leading-6 text-stone-600">{{ $promotion->short_description }}</p>@endif
                        @if ($promotion->end_at)<p class="mt-4 text-xs text-stone-500">Berlaku sampai {{ $promotion->end_at->translatedFormat('d F Y') }}</p>@endif
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-10">{{ $promotions->links() }}</div>
    @endif
</x-layouts.public>
