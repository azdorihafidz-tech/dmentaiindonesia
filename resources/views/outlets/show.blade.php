<x-layouts.public :metadata="$metadata">
    <nav aria-label="Breadcrumb" class="mb-6 text-sm text-stone-500"><a class="text-link" href="{{ route('home') }}">Beranda</a> <span aria-hidden="true">/</span> <a class="text-link" href="{{ route('outlets.index') }}">Outlet</a> <span aria-hidden="true">/</span> {{ $outlet->name }}</nav>
    <article class="mx-auto mt-6 max-w-3xl">
        @if ($outlet->image)<img src="{{ $outlet->image->url }}" alt="{{ $outlet->image->alt_text ?: $outlet->name }}" class="mb-8 aspect-[16/9] w-full rounded-2xl object-cover">@endif
        <h1 class="text-4xl font-black tracking-tight sm:text-5xl">{{ $outlet->name }}</h1>
        @if ($outlet->address)<p class="mt-5 whitespace-pre-line text-lg leading-8 text-stone-700">{{ $outlet->address }}</p>@endif
        @if ($outlet->business_hours)<section class="mt-6"><h2 class="font-bold">Jam operasional</h2><p class="mt-2 whitespace-pre-line text-stone-600">{{ $outlet->business_hours }}</p></section>@endif
        <div class="mt-5 flex flex-wrap gap-4 text-sm font-semibold">
            @if ($outlet->phone)<a href="tel:{{ $outlet->phone }}" class="text-orange-700">Telepon outlet</a>@endif
            @if ($outlet->whatsapp)<a href="https://wa.me/{{ preg_replace('/\D+/', '', $outlet->whatsapp) }}" class="text-orange-700" rel="noopener noreferrer">WhatsApp outlet</a>@endif
            @if ($outlet->google_maps_url)<a href="{{ $outlet->google_maps_url }}" class="text-orange-700" target="_blank" rel="noopener noreferrer">Lihat peta</a>@endif
        </div>
        @if ($outlet->description)<div class="article-content mt-8 leading-8 text-stone-700">{!! app(\App\Services\PageContentSanitizer::class)->sanitize($outlet->description) !!}</div>@endif
    </article>
</x-layouts.public>
