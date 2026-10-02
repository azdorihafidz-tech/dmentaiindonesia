<x-layouts.public :metadata="$metadata">
    <section class="page-intro"><p class="eyebrow">Momen D’Mentai</p><h1>Galeri</h1><p>Foto yang dipilih dan dibagikan oleh D’Mentai.</p></section>
    @if ($items->isEmpty())
        <p class="empty-state">Belum ada foto yang ditampilkan.</p>
    @else
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-5 lg:grid-cols-4">
            @foreach ($items as $item)
                <figure class="content-card">
                    <img src="{{ $item->image->url }}" alt="{{ $item->alt_text ?: $item->image->alt_text ?: $item->title }}" class="aspect-square w-full object-cover" loading="lazy">
                    @if ($item->caption || $item->title)<figcaption class="card-body text-sm text-stone-700">{{ $item->caption ?: $item->title }}</figcaption>@endif
                </figure>
            @endforeach
        </div>
        <div class="mt-10">{{ $items->links() }}</div>
    @endif
</x-layouts.public>
