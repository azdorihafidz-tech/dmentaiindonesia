<x-layouts.public :metadata="$metadata">
    <section class="page-intro"><p class="eyebrow">Kunjungi kami</p><h1>Outlet</h1><p>Informasi lokasi yang dikelola D’Mentai.</p></section>
    @if ($outlets->isEmpty())
        <p class="rounded-2xl border border-dashed border-stone-300 bg-white p-8 text-stone-600">Informasi outlet akan ditampilkan di sini.</p>
    @else
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($outlets as $outlet)
                <a href="{{ route('outlets.show', $outlet->slug) }}" class="overflow-hidden rounded-2xl border border-stone-200 bg-white hover:shadow-md">
                    @if ($outlet->image)<img src="{{ $outlet->image->url }}" alt="{{ $outlet->image->alt_text ?: $outlet->name }}" class="aspect-[4/3] w-full object-cover" loading="lazy">@endif
                    <div class="card-body"><h2>{{ $outlet->name }}</h2>@if ($outlet->address)<p>{{ $outlet->address }}</p>@endif @if($outlet->business_hours)<p class="small-note whitespace-pre-line">{{ $outlet->business_hours }}</p>@endif <div class="mt-4 flex flex-wrap gap-3 text-sm font-bold text-orange-800">@if($outlet->phone)<span>{{ $outlet->phone }}</span>@endif @if($outlet->google_maps_url)<span>Lihat peta ↗</span>@endif</div></div>
                </a>
            @endforeach
        </div>
        <div class="mt-10">{{ $outlets->links() }}</div>
    @endif
</x-layouts.public>
