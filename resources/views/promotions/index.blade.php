<x-layouts.public :metadata="$metadata">
    <section class="page-intro"><p class="eyebrow">Info terbaru</p><h1>Promosi</h1><p>Promosi yang saat ini tersedia.</p></section>
    @if ($promotions->isEmpty())
        <p class="rounded-2xl border border-dashed border-stone-300 bg-white p-8 text-stone-600">Belum ada promosi yang tersedia.</p>
    @else
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($promotions as $promotion)
                <a href="{{ route('promotions.show', $promotion->slug) }}" class="overflow-hidden rounded-2xl border border-stone-200 bg-white hover:shadow-md">
                    @if ($promotion->banner)<img src="{{ $promotion->banner->url }}" alt="{{ $promotion->banner->alt_text ?: $promotion->title }}" class="aspect-[16/10] w-full object-cover" loading="lazy">@endif
                    <div class="card-body"><h2>{{ $promotion->title }}</h2>
                        @if ($promotion->short_description)<p>{{ $promotion->short_description }}</p>@endif
                        @if ($promotion->start_at || $promotion->end_at)<p class="small-note">@if ($promotion->start_at){{ $promotion->start_at->locale('id')->translatedFormat('d M Y') }}@endif @if($promotion->start_at && $promotion->end_at)–@endif @if ($promotion->end_at){{ $promotion->end_at->locale('id')->translatedFormat('d M Y') }}@endif</p>@endif
                        <span class="mt-4 font-bold text-orange-800">Lihat detail →</span>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-10">{{ $promotions->links() }}</div>
    @endif
</x-layouts.public>
