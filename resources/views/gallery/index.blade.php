<x-layouts.public :metadata="$metadata">
    <div class="mb-10 max-w-2xl">
        <p class="text-sm font-bold uppercase tracking-[0.2em] text-orange-700">D'Mentai</p>
        <h1 class="mt-2 text-4xl font-black tracking-tight sm:text-5xl">Galeri</h1>
    </div>
    @if ($items->isEmpty())
        <p class="rounded-2xl border border-dashed border-stone-300 bg-white p-8 text-stone-600">Belum ada foto yang ditampilkan.</p>
    @else
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-5 lg:grid-cols-4">
            @foreach ($items as $item)
                <figure class="overflow-hidden rounded-2xl border border-stone-200 bg-white">
                    <img src="{{ $item->image->url }}" alt="{{ $item->alt_text ?: $item->image->alt_text ?: $item->title }}" class="aspect-square w-full object-cover" loading="lazy">
                    @if ($item->caption || $item->title)<figcaption class="p-3 text-sm text-stone-700 sm:p-4">{{ $item->caption ?: $item->title }}</figcaption>@endif
                </figure>
            @endforeach
        </div>
        <div class="mt-10">{{ $items->links() }}</div>
    @endif
</x-layouts.public>
