<x-layouts.public :metadata="$metadata">
    <section class="page-intro"><p class="eyebrow">Kunjungi kami</p><h1>Outlet</h1><p>Informasi lokasi yang dikelola D’Mentai.</p></section>
    @if ($outlets->isEmpty())
        <p class="rounded-2xl border border-dashed border-stone-300 bg-white p-8 text-stone-600">Informasi outlet akan ditampilkan di sini.</p>
    @else
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($outlets as $outlet)
                <article class="content-card outlet-card">
                    @php($whatsappUrl = app(\App\Services\WhatsAppLink::class)->url($outlet->whatsapp))
                    @if ($outlet->image)<a href="{{ route('outlets.show', $outlet->slug) }}" class="block"><img src="{{ $outlet->image->url }}" alt="{{ $outlet->image->alt_text ?: $outlet->name }}" class="card-image" loading="lazy"></a>@endif
                    <div class="card-body">
                        <h2><a href="{{ route('outlets.show', $outlet->slug) }}" class="text-link">{{ $outlet->name }}</a></h2>
                        @php($address = collect([$outlet->address, $outlet->city, $outlet->province, $outlet->country])->filter()->implode(', '))
                        @if ($address)<p>{{ $address }}</p>@endif
                        @if($outlet->business_hours)<p class="small-note whitespace-pre-line">{{ $outlet->business_hours }}</p>@endif
                        <div class="mt-4 flex flex-wrap gap-2">
                            @if($outlet->phone)<a href="tel:{{ $outlet->phone }}" class="button button-outline">Telepon</a>@endif
                            @if($whatsappUrl)<a href="{{ $whatsappUrl }}" class="button button-outline" target="_blank" rel="noopener noreferrer">WhatsApp</a>@endif
                            @if($outlet->google_maps_link)<a href="{{ $outlet->google_maps_link }}" class="button button-light" target="_blank" rel="noopener noreferrer" aria-label="Buka lokasi {{ $outlet->name }} di Google Maps">Lihat peta ↗</a>@endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
        <div class="mt-10">{{ $outlets->links() }}</div>
    @endif
</x-layouts.public>
