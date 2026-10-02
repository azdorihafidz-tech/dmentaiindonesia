<x-layouts.public :metadata="$metadata">
    <div class="mx-auto mb-10 max-w-2xl text-center">
        <p class="text-sm font-bold uppercase tracking-[0.2em] text-orange-700">Bantuan</p>
        <h1 class="mt-2 text-4xl font-black tracking-tight sm:text-5xl">Pertanyaan umum</h1>
    </div>
    @if ($faqs->isEmpty())
        <p class="mx-auto max-w-2xl rounded-2xl border border-dashed border-stone-300 bg-white p-8 text-center text-stone-600">Belum ada pertanyaan yang ditampilkan.</p>
    @else
        <div class="mx-auto max-w-3xl space-y-8">
            @foreach ($faqs->groupBy(fn ($faq) => $faq->category ?: 'Pertanyaan umum') as $category => $items)
                <section aria-label="{{ $category }}">
                    @if ($faqs->pluck('category')->filter()->unique()->count() > 1)<h2 class="mb-3 text-lg font-bold">{{ $category }}</h2>@endif
                    <div class="space-y-3">
                        @foreach ($items as $faq)
                            <details class="group rounded-xl border border-stone-200 bg-white p-5">
                                <summary class="cursor-pointer list-none pr-7 font-semibold focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-orange-600">
                                    {{ $faq->question }}
                                    <span aria-hidden="true" class="float-right text-orange-700 group-open:rotate-45">+</span>
                                </summary>
                                <p class="mt-4 whitespace-pre-line leading-7 text-stone-600">{{ $faq->answer }}</p>
                            </details>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    @endif
</x-layouts.public>
