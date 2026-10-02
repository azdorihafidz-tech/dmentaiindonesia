<x-layouts.public :metadata="$metadata">
    <div class="mb-10 max-w-2xl">
        <p class="text-sm font-bold uppercase tracking-[0.2em] text-orange-700">D'Mentai</p>
        <h1 class="mt-2 text-4xl font-black tracking-tight sm:text-5xl">Outlet</h1>
        <p class="mt-4 text-stone-600">Temukan outlet D'Mentai.</p>
    </div>
    @if ($outlets->isEmpty())
        <p class="rounded-2xl border border-dashed border-stone-300 bg-white p-8 text-stone-600">Informasi outlet akan ditampilkan di sini.</p>
    @else
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($outlets as $outlet)
                <a href="{{ route('outlets.show', $outlet->slug) }}" class="overflow-hidden rounded-2xl border border-stone-200 bg-white hover:shadow-md">
                    @if ($outlet->image)<img src="{{ $outlet->image->url }}" alt="{{ $outlet->image->alt_text ?: $outlet->name }}" class="aspect-[4/3] w-full object-cover" loading="lazy">@endif
                    <div class="p-5"><h2 class="text-xl font-bold">{{ $outlet->name }}</h2>@if ($outlet->address)<p class="mt-2 line-clamp-3 text-sm leading-6 text-stone-600">{{ $outlet->address }}</p>@endif</div>
                </a>
            @endforeach
        </div>
        <div class="mt-10">{{ $outlets->links() }}</div>
    @endif
</x-layouts.public>
